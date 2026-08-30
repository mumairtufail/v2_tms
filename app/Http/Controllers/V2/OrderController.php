<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Order;
use App\Models\Customer;
use App\Services\OrderFormDataBuilder;
use App\Services\OrderService;
use App\Services\OrderUpdateService;
use App\Services\PluginService;
use App\Plugins\QuickBooks\Services\QuickBooksService;
use App\Plugins\QuickBooks\Services\ApiClient;
use App\Support\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    protected $orderService;
    protected $pluginService;

    public function __construct(OrderService $orderService, PluginService $pluginService)
    {
        $this->orderService = $orderService;
        $this->pluginService = $pluginService;
    }

    /**
     * Display a listing of orders.
     */
    public function index(Request $request, Company $company)
    {
        $query = Order::where('company_id', $company->id)
            ->with(['customer', 'stops', 'manifest', 'quote.costs']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhere('ref_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_po_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function($c) use ($search) {
                      $c->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('v2.company.orders.index', compact('company', 'orders'));
    }

    /**
     * Store a newly created order (Draft).
     */
    public function store(Request $request, Company $company)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_type' => 'required|in:point_to_point,single_shipper,single_consignee,sequence',
        ]);

        $order = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $company) {
            $customer = Customer::findOrFail($request->customer_id);

            $order = Order::create([
                'customer_id'  => $customer->id,
                'order_type'   => $request->order_type,
                'company_id'   => $company->id,
                'status'       => 'draft',
                'order_number' => 'TEMP-' . uniqid(),
            ]);

            // Format: {COMPANY_SC}-{CUSTOMER_SC}-{ID}  e.g. INVO-AFSL-45
            $companySc  = strtoupper($company->shortcode ?: Str::upper(Str::substr($company->name, 0, 4)));
            $customerSc = strtoupper($customer->short_code ?: Str::upper(Str::substr($customer->name, 0, 4)));
            $order->order_number = "{$companySc}-{$customerSc}-{$order->id}";
            $order->save();

            return $order;
        });

        Toast::success('New order draft created.');
        return redirect()->route('v2.orders.edit', ['company' => $company->slug, 'order' => $order->id]);
    }

    /**
     * Show the form for editing the order.
     */
    public function edit(Company $company, Order $order, OrderFormDataBuilder $formDataBuilder)
    {
        $viewData = $formDataBuilder->build($company, $order);

        return view('v2.company.orders.form', $viewData);
    }

    /**
     * Update the order.
     */
    public function update(Request $request, Company $company, Order $order, OrderUpdateService $orderUpdateService)
    {
        return $orderUpdateService->update($request, $company, $order, [
            'redirect_route' => 'v2.orders.edit',
        ]);
    }

    /**
     * Remove the order.
     */
    public function destroy(Company $company, Order $order)
    {
        $orderNumber = $order->order_number;
        $order->delete();

        Toast::success("Order {$orderNumber} deleted successfully.");
        return redirect()->route('v2.orders.index', $company->slug);
    }

    /**
     * Bulk delete orders.
     */
    public function bulkDestroy(Request $request, Company $company)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:orders,id'
        ]);

        $ids = $request->input('ids');
        $count = count($ids);

        // Ensure orders belong to the company
        Order::where('company_id', $company->id)
            ->whereIn('id', $ids)
            ->delete();

        Toast::success("{$count} order(s) deleted successfully.");
        return back();
    }

    /**
     * Search customers for the creation modal.
     */
    public function searchCustomers(Request $request, Company $company)
    {
        $search = $request->query('q');
        $customers = Customer::where('company_id', $company->id)
            ->where('name', 'LIKE', "%{$search}%")
            ->limit(10)
            ->get(['id', 'name', 'address', 'city', 'state', 'postal_code']);

        return response()->json($customers);
    }

    /**
     * Sync Order to QuickBooks.
     */
    public function syncToQuickBooks(Company $company, Order $order)
    {
        try {
            $order->load(['customer', 'quote.costs']);
            
            $config = $this->pluginService->getConfiguration($company->id, 'quickbooks');
            
            if (!$config || !($config->is_active ?? false)) {
                return back()->with('error', 'QuickBooks plugin is not active or configured.');
            }

            $configuration = $config->configuration;
            $configuration['config_id'] = $config->id; // For token refresh persistence

            $apiClient = new ApiClient($configuration);
            $qbService = new QuickBooksService($apiClient);

            $qbInvoice = $qbService->createInvoice($order);

            if ($qbInvoice && isset($qbInvoice['Id'])) {
                $order->update(['quickbooks_invoice_id' => $qbInvoice['Id']]);
                Toast::success('Order synced to QuickBooks (Invoice created)!');
            } else {
                Toast::error('Failed to create invoice in QuickBooks.');
            }

            return back();
        } catch (\Exception $e) {
            return back()->with('error', 'QuickBooks sync failed: ' . $e->getMessage());
        }
    }
}
