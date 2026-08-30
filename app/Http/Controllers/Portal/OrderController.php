<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Order;
use App\Services\OrderFormDataBuilder;
use App\Services\OrderUpdateService;
use App\Services\Portal\CustomerPortalService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $customer = Auth::guard('customer')->user();

        $orders = $this->portalService->getOrders($customer, [
            'search' => $request->search,
            'status' => $request->status,
        ]);

        return view('portal.orders.index', compact('company', 'customer', 'orders'));
    }

    public function store(Company $company): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();
        $this->portalService->assertCustomerBelongsToCompany($customer, $company);

        $order = DB::transaction(function () use ($customer, $company) {
            $order = Order::create([
                'customer_id' => $customer->id,
                'order_type' => 'point_to_point',
                'company_id' => $company->id,
                'status' => 'draft',
                'order_number' => 'TEMP-' . uniqid(),
            ]);

            $companySc = strtoupper($company->shortcode ?: Str::upper(Str::substr($company->name, 0, 4)));
            $customerSc = strtoupper($customer->short_code ?: Str::upper(Str::substr($customer->name, 0, 4)));
            $order->order_number = "{$companySc}-{$customerSc}-{$order->id}";
            $order->save();

            return $order;
        });

        Toast::success('New order draft created.');

        return redirect()->route('portal.orders.edit', ['company' => $company->slug, 'order' => $order->id]);
    }

    public function edit(Company $company, Order $order): View
    {
        $customer = Auth::guard('customer')->user();
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
        $customer = Auth::guard('customer')->user();
        $this->portalService->assertOrderEditableByCustomer($order, $customer);

        return $this->orderUpdateService->update($request, $company, $order, [
            'portal' => true,
            'force_order_type' => 'point_to_point',
            'redirect_route' => 'portal.orders.edit',
        ]);
    }

    public function show(Company $company, Order $order): View
    {
        $customer = Auth::guard('customer')->user();

        $this->portalService->assertOrderBelongsToCustomer($order, $customer);

        $order = $this->portalService->getOrderDetail($customer, $order->id);

        return view('portal.orders.show', compact('company', 'customer', 'order'));
    }
}
