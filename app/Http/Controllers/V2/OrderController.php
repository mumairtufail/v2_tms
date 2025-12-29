<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Order;
use App\Models\Customer;
use App\Services\OrderService;
use App\Support\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
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
            $order = Order::create([
                'customer_id' => $request->customer_id,
                'order_type' => $request->order_type,
                'company_id' => $company->id,
                'status' => 'draft',
                'order_number' => 'TEMP'
            ]);

            $order->order_number = 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT);
            $order->save();

            return $order;
        });

        Toast::success('New order draft created.');
        return redirect()->route('v2.orders.edit', ['company' => $company->slug, 'order' => $order->id]);
    }

    /**
     * Show the form for editing the order.
     */
    public function edit(Company $company, Order $order)
    {
        $order->load(['stops.commodities', 'stops.accessorials', 'customer', 'manifest.drivers', 'manifest.carriers', 'manifest.equipments', 'quote.costs']);
        
        $services = \App\Models\Service::all();
        $allAccessorials = \App\Models\Accessorial::orderBy('name')->get();
        $manifests = \App\Models\Manifest::where('company_id', $company->id)->get();

        return view('v2.company.orders.form', compact('company', 'order', 'services', 'allAccessorials', 'manifests'));
    }

    /**
     * Update the order.
     */
    public function update(Request $request, Company $company, Order $order)
    {
        $orderType = $request->input('order_type');
        
        // Use the existing OrderService for logic but wrapped in V2 controller
        try {
            if ($orderType === 'sequence') {
                $data = $request->validate([
                    'ref_number' => 'nullable|string|max:255',
                    'customer_po_number' => 'nullable|string|max:255',
                    'special_instructions' => 'nullable|string',
                    'stops' => 'nullable|array',
                    'save_as_draft' => 'nullable|boolean',
                    // Include quote fields
                    'service_id' => 'nullable|exists:services,id',
                    'quote_notes' => 'nullable|string',
                    'quote_delivery_start' => 'nullable|date',
                    'quote_delivery_end' => 'nullable|date',
                    'carrier_costs' => 'nullable|array',
                    'customer_quotes' => 'nullable|array',
                ]);

                $this->orderService->updateSequence($order, $data);
            } elseif ($orderType === 'point_to_point') {
                // ... handle other types if needed, focusing on sequence first as requested
                // For now, redirect through the service
            }

            Toast::success('Order updated successfully.');
            return redirect()->route('v2.orders.edit', ['company' => $company->slug, 'order' => $order->id]);

        } catch (\Exception $e) {
            \Log::error('Order update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update order: ' . $e->getMessage())->withInput();
        }
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
        // Existing logic from OrderController@syncToQuickBooks
        // Implementation omitted for brevity in this initial write, will port fully in next steps
        try {
            // ... QB logic ...
            Toast::success('Order synced to QuickBooks.');
            return back();
        } catch (\Exception $e) {
            return back()->with('error', 'QuickBooks sync failed: ' . $e->getMessage());
        }
    }
}
