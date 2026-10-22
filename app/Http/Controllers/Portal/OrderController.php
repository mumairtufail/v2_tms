<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Order;
use App\Services\ActivityLogListingService;
use App\Services\CustomerCreditService;
use App\Services\OrderFormDataBuilder;
use App\Services\OrderUpdateService;
use App\Services\Portal\CustomerPortalService;
use App\Support\Toast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected CustomerPortalService $portalService,
        protected OrderFormDataBuilder $formDataBuilder,
        protected OrderUpdateService $orderUpdateService,
    ) {}

    public function index(Request $request, Company $company): View
    {
        $customer = app('current.customer');

        $orders = $this->portalService->getOrders($customer, [
            'search' => $request->search,
            'status' => $request->status,
        ]);

        return view('portal.orders.index', compact('company', 'customer', 'orders'));
    }

    /**
     * The New Order screen, for an order that does not exist yet.
     *
     * "New Order" used to POST straight to store(), which inserted a draft before
     * the customer had typed anything. Every click left an empty draft behind,
     * including when they backed out. Nothing is written now until they press
     * Save as Draft or Submit Order on this screen.
     */
    public function create(Request $request, Company $company): View
    {
        $customer = app('current.customer');
        $this->portalService->assertCustomerBelongsToCompany($customer, $company);

        $order = (new Order())->forceFill([
            'customer_id' => $customer->id,
            'company_id' => $company->id,
            'order_type' => 'point_to_point',
            'created_via' => 'portal',
            'status' => 'draft',
        ]);
        $order->setRelation('customer', $customer);

        $viewData = $this->formDataBuilder->build($company, $order, allowTypeOverride: false);

        // A save the server turned down comes back here; put back what they had
        // typed rather than an empty form.
        $previousStops = json_decode((string) $request->old('stops', ''), true);
        if (is_array($previousStops) && $previousStops !== []) {
            $viewData['stopsData'] = $previousStops;
        }

        return view('v2.company.orders.form', array_merge($viewData, [
            'isPortal' => true,
            'customer' => $customer,
        ]));
    }

    /**
     * First save of a new order, from Save as Draft or Submit Order on the
     * create screen.
     *
     * The order is created and filled inside one transaction. If anything in
     * the save is rejected it is rolled back, so a failed first save never
     * leaves a half-made order behind, and the customer is returned to the form
     * with their input and the reason.
     */
    public function store(Request $request, Company $company): RedirectResponse
    {
        $customer = app('current.customer');
        $this->portalService->assertCustomerBelongsToCompany($customer, $company);

        // A tab still showing the old one-click New Order button posts with no
        // form at all. Send it to the create screen instead of making an empty draft.
        if (! $request->has('stops')) {
            return redirect()->route('portal.orders.create', ['company' => $company->slug]);
        }

        $wantsDraft = $request->input('save_as_draft') === '1';

        // At the credit limit a submission is kept as a draft rather than lost,
        // which is what the message on the existing-order path already promises.
        $creditService = app(CustomerCreditService::class);
        $heldForCredit = ! $wantsDraft && $creditService->isOverLimit($customer);
        if ($heldForCredit) {
            $request->merge(['save_as_draft' => '1']);
        }

        DB::beginTransaction();

        try {
            $order = Order::create([
                'customer_id' => $customer->id,
                'order_type' => 'point_to_point',
                'created_via' => 'portal',
                'company_id' => $company->id,
                'status' => 'draft',
                'order_number' => 'TEMP-' . uniqid(),
            ]);

            $companySc = strtoupper($company->shortcode ?: Str::upper(Str::substr($company->name, 0, 4)));
            $customerSc = strtoupper($customer->short_code ?: Str::upper(Str::substr($customer->name, 0, 4)));
            $order->order_number = "{$companySc}-{$customerSc}-{$order->id}";
            $order->save();

            $response = $this->orderUpdateService->update($request, $company, $order, [
                'portal' => true,
                'force_order_type' => 'point_to_point',
                'redirect_route' => 'portal.orders.edit',
            ]);

            // update() reports a rejected save as a redirect back with errors
            // rather than an exception; a successful one redirects to this order.
            $succeeded = in_array($response->getTargetUrl(), [
                route('portal.orders.edit', ['company' => $company->slug, 'order' => $order->id]),
                route('portal.orders.show', ['company' => $company->slug, 'order' => $order->id]),
            ], true);

            if (! $succeeded) {
                DB::rollBack();

                Log::channel('portal')->info('New portal order not created: first save was rejected', [
                    'customer_id' => $customer->id,
                    'company_id' => $company->id,
                    'save_as_draft' => $wantsDraft,
                ]);

                // The errors are already flashed by update(); keep the input too.
                return redirect()
                    ->route('portal.orders.create', ['company' => $company->slug])
                    ->withInput();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::channel('portal')->error('New portal order could not be created', [
                'customer_id' => $customer->id,
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('portal.orders.create', ['company' => $company->slug])
                ->withInput()
                ->with('error', 'Your order could not be saved. Please try again, and contact us if it keeps happening.');
        }

        Log::channel('portal')->info('New portal order created', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer_id' => $customer->id,
            'status' => $order->fresh()?->status,
            'held_for_credit' => $heldForCredit,
        ]);

        $request->attributes->set('activity_order_id', $order->id);
        $request->attributes->set('activity_description', $wantsDraft || $heldForCredit
            ? 'Created the order as a draft'
            : 'Created and submitted the order');

        // update() flashed its generic "Order saved as draft." / "submitted"
        // message; replace it with one that names the new order.
        session()->forget('success');

        if ($heldForCredit) {
            $usage = $creditService->usage($customer);

            return redirect()
                ->route('portal.orders.edit', ['company' => $company->slug, 'order' => $order->id])
                ->with('error', sprintf(
                    'This order cannot be submitted: the account has reached its credit limit ($%s of $%s used). It is saved as a draft. Please contact us to continue.',
                    number_format($usage['used'], 2),
                    number_format($usage['limit'], 2),
                ));
        }

        return $response->with('success', $wantsDraft
            ? "Order {$order->order_number} saved as a draft."
            : "Order {$order->order_number} submitted.");
    }

    public function edit(Company $company, Order $order): View
    {
        $customer = app('current.customer');
        $this->portalService->assertOrderEditableByCustomer($order, $customer);

        $order->order_type = 'point_to_point';
        $viewData = $this->formDataBuilder->build($company, $order, allowTypeOverride: false);

        return view('v2.company.orders.form', array_merge($viewData, [
            'isPortal' => true,
            'customer' => $customer,
        ]));
    }

    public function update(Request $request, Company $company, Order $order): RedirectResponse
    {
        $customer = app('current.customer');
        $this->portalService->assertOrderEditableByCustomer($order, $customer);

        return $this->orderUpdateService->update($request, $company, $order, [
            'portal' => true,
            'force_order_type' => 'point_to_point',
            'redirect_route' => 'portal.orders.edit',
        ]);
    }

    public function show(Company $company, Order $order): View
    {
        $customer = app('current.customer');

        $this->portalService->assertOrderBelongsToCustomer($order, $customer);

        $order = $this->portalService->getOrderDetail($customer, $order->id);

        return view('portal.orders.show', compact('company', 'customer', 'order'));
    }

    public function activityLogs(Company $company, Order $order, ActivityLogListingService $listingService): JsonResponse
    {
        $customer = app('current.customer');
        $this->portalService->assertOrderBelongsToCustomer($order, $customer);

        return response()->json([
            'order_number' => $order->order_number,
            'logs' => $listingService->forOrder($company, $order),
        ]);
    }
}
