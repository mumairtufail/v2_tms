<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Accessorial;
use Illuminate\Http\Request;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UpdatePointToPointOrderRequest;
use App\Http\Requests\UpdateSingleShipperOrderRequest;
use App\Http\Requests\UpdateSingleConsigneeOrderRequest;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
public function store(Request $request)
{
    $order = DB::transaction(function () use ($request) {
        // First, create the order with the fields that are available.
        // The 'order_number' is left out for now.
        $order = new Order([
            'customer_id' => $request->input('customer_id'),
            'order_type' => $request->input('order_type', 'point_to_point'),
            'company_id' => Auth::user()->company_id,
            'status' => 'draft',
            // Temporarily set a placeholder order_number
            'order_number' => 'TEMP' 
        ]);
        $order->save(); // Save to get an ID

        // Now, generate the real order_number and update the record.
        $order->order_number = 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT);
        $order->save();

        return $order;
    });

    return redirect()->route('orders.edit', $order->id)->with('success', 'New order draft created.');
}
    public function index(Request $request)
    {
        $query = Order::where('company_id', Auth::user()->company_id)
                     ->with(['customer', 'stops', 'manifest']);
                     
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q->where('order_number', 'LIKE', "%{$search}%")
                                    ->orWhereHas('customer', fn($c) => $c->where('name', 'LIKE', "%{$search}%")));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(20);
        return view('dashboard.orders.index', compact('orders'));
    }

    public function create()
    {
        $order = DB::transaction(function () {
            $order = Order::create(['status' => 'draft', 'order_type' => 'point_to_point', 'company_id' => Auth::user()->company_id]);
            $order->order_number = 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT);
            $order->save();
            return $order;
        });
        return redirect()->route('orders.edit', $order->id)->with('success', 'New order draft created.');
    }

    public function edit(Order $order)
    {
        if ($order->company_id !== Auth::user()->company_id) { abort(403); }
        $order->load('stops.commodities', 'stops.accessorials', 'customer', 'manifest.drivers', 'manifest.carriers', 'manifest.equipments', 'quote.costs');
        $allAccessorials = Accessorial::orderBy('name')->get();
        
        // Prepare existing stops data for sequence orders
        $hasExistingStops = $order->order_type === 'sequence' && $order->stops->count() > 0;
        $existingStops = $hasExistingStops ? $order->stops->sortBy('sequence_number') : collect();
        $services = \App\Models\Service::all();
        
        return view('dashboard.orders.edit', compact('order', 'allAccessorials', 'hasExistingStops', 'existingStops', 'services')); 
    }

    public function update(Request $request, Order $order)
    {
        if ($order->company_id !== Auth::user()->company_id) { abort(403); }
        $orderType = $request->input('order_type');
        
        // Debug logging
        \Log::info('Order update request received', [
            'order_id' => $order->id,
            'order_type_received' => $orderType,
            'all_request_data' => $request->all()
        ]);

        if ($orderType === 'point_to_point') {
            $data = $this->validate($request, app(UpdatePointToPointOrderRequest::class)->rules());
            $this->orderService->updatePointToPoint($order, $data);
            return redirect()->route('orders.edit', $order)->with('success', 'Point-to-Point Order updated successfully!');
        }

        if ($orderType === 'single_shipper') {
            $data = $this->validate($request, app(UpdateSingleShipperOrderRequest::class)->rules());
            $this->orderService->updateSingleShipper($order, $data);
            return redirect()->route('orders.edit', $order)->with('success', 'Single Shipper Order updated successfully!');
        }

        if ($orderType === 'single_consignee') {
            $data = $this->validate($request, app(UpdateSingleConsigneeOrderRequest::class)->rules());
            $this->orderService->updateSingleConsignee($order, $data);
            return redirect()->route('orders.edit', $order)->with('success', 'Single Consignee Order updated successfully!');
        }

        if ($orderType === 'sequence') {
            // Store whatever data is provided without strict validation
            $data = $request->validate([
                'ref_number' => 'nullable|string|max:255',
                'customer_po_number' => 'nullable|string|max:255',
                'special_instructions' => 'nullable|string',
                'stops' => 'nullable|array',
                'stops.*' => 'nullable|array',
                // Shipper fields - all optional
                'stops.*.shipper_company_name' => 'nullable|string|max:255',
                'stops.*.shipper_address_1' => 'nullable|string|max:255',
                'stops.*.shipper_address_2' => 'nullable|string|max:255',
                'stops.*.shipper_city' => 'nullable|string|max:255',
                'stops.*.shipper_state' => 'nullable|string|max:255',
                'stops.*.shipper_zip' => 'nullable|string|max:20',
                'stops.*.shipper_country' => 'nullable|string|max:255',
                'stops.*.shipper_contact_name' => 'nullable|string|max:255',
                'stops.*.shipper_phone' => 'nullable|string|max:20',
                'stops.*.shipper_contact_email' => 'nullable|string|max:255',
                'stops.*.shipper_notes' => 'nullable|string',
                'stops.*.shipper_opening_time' => 'nullable|string',
                'stops.*.shipper_closing_time' => 'nullable|string',
                // Ready time fields
                'stops.*.ready_start_time' => 'nullable|string',
                'stops.*.ready_end_time' => 'nullable|string',
                'stops.*.ready_appointment' => 'nullable|boolean',
                // Consignee fields - all optional
                'stops.*.consignee_company_name' => 'nullable|string|max:255',
                'stops.*.consignee_address_1' => 'nullable|string|max:255',
                'stops.*.consignee_address_2' => 'nullable|string|max:255',
                'stops.*.consignee_city' => 'nullable|string|max:255',
                'stops.*.consignee_state' => 'nullable|string|max:255',
                'stops.*.consignee_zip' => 'nullable|string|max:20',
                'stops.*.consignee_country' => 'nullable|string|max:255',
                'stops.*.consignee_contact_name' => 'nullable|string|max:255',
                'stops.*.consignee_phone' => 'nullable|string|max:20',
                'stops.*.consignee_contact_email' => 'nullable|email|max:255',
                'stops.*.consignee_notes' => 'nullable|string',
                'stops.*.consignee_opening_time' => 'nullable|string',
                'stops.*.consignee_closing_time' => 'nullable|string',
                // Delivery time fields
                'stops.*.delivery_start_time' => 'nullable|string',
                'stops.*.delivery_end_time' => 'nullable|string',
                'stops.*.delivery_appointment' => 'nullable|boolean',
                // Additional information fields
                'stops.*.customs_broker' => 'nullable|string|max:255',
                'stops.*.port_of_entry' => 'nullable|string|max:255',
                'stops.*.declared_value' => 'nullable|string',
                'stops.*.currency' => 'nullable|string|max:10',
                'stops.*.container_number' => 'nullable|string|max:255',
                'stops.*.ref_number' => 'nullable|string|max:255',
                'stops.*.customer_po_number' => 'nullable|string|max:255',
                // Commodities
                'stops.*.commodities' => 'nullable|array',
                'stops.*.commodities.*.description' => 'nullable|string|max:255',
                'stops.*.commodities.*.quantity' => 'nullable|string',
                'stops.*.commodities.*.weight' => 'nullable|string',
                'stops.*.commodities.*.length' => 'nullable|string',
                'stops.*.commodities.*.width' => 'nullable|string',
                'stops.*.commodities.*.height' => 'nullable|string',
                // Accessorials
                'stops.*.accessorials' => 'nullable|array',
                // Manifest assignment
                'stops.*.manifest_id' => 'nullable|exists:manifests,id',
                
                // Quote Fields
                'service_id' => 'nullable|exists:services,id',
                'quote_notes' => 'nullable|string',
                'quote_delivery_start' => 'nullable|date',
                'quote_delivery_end' => 'nullable|date',
                'carrier_costs' => 'nullable|array',
                'customer_quotes' => 'nullable|array',
            ]);
            
            $this->orderService->updateSequence($order, $data);
            return redirect()->route('orders.edit', $order)->with('success', 'Sequence Order updated successfully!');
        }

        return redirect()->back()->with('error', 'Invalid order type submitted.');
    }

    public function destroy(Order $order)
    {
        if ($order->company_id !== Auth::user()->company_id) { abort(403); }
        
        $orderNumber = $order->order_number;
        $order->delete();
        
        return redirect()->route('orders.index')->with('success', "Order {$orderNumber} has been deleted successfully.");
    }


     public function saveSingleShipper(UpdateSingleShipperOrderRequest $request, Order $order)
    {
        // 1. Authorize the request
        if ($order->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized Action');
        }

        // 2. The data is already validated by UpdateSingleShipperOrderRequest.
        //    Now, delegate the business logic to the OrderService.
        $this->orderService->updateSingleShipper($order, $request->validated());
        
        // 3. Redirect back with a success message.
        return redirect()->route('orders.edit', $order)->with('success', 'Single Shipper Order updated successfully!');
    }

     public function savePointToPoint(UpdatePointToPointOrderRequest $request, Order $order)
    {
        if ($order->company_id !== Auth::user()->company_id) { abort(403); }
        $this->orderService->updatePointToPoint($order, $request->validated());
        return redirect()->route('orders.edit', $order)->with('success', 'Point-to-Point Order updated successfully!');
    }

    public function saveSingleConsignee(UpdateSingleConsigneeOrderRequest $request, Order $order)
    {
        if ($order->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized Action');
        }

        $this->orderService->updateSingleConsignee($order, $request->validated());
        
        return redirect()->route('orders.edit', $order)->with('success', 'Single Consignee Order updated successfully!');
    }

    /**
     * Assign customer to order
     */
    public function assignCustomer(Request $request, Order $order)
    {
        try {
            if ($order->company_id !== Auth::user()->company_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $request->validate([
                'customer_id' => 'required|exists:customers,id'
            ]);

            $order->update(['customer_id' => $request->customer_id]);

            return response()->json([
                'success' => true,
                'message' => 'Customer assigned successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign customer'
            ], 500);
        }
    }

    /**
     * Assign manifest to order
     */
    public function assignManifest(Request $request, Order $order)
    {
        try {
            if ($order->company_id !== Auth::user()->company_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $request->validate([
                'manifest_id' => 'required|exists:manifests,id'
            ]);

            $order->update(['manifest_id' => $request->manifest_id]);

            return response()->json([
                'success' => true,
                'message' => 'Manifest assigned successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign manifest'
            ], 500);
        }
    }
    /**
     * Save a quote for the order
     */
    public function saveQuote(Request $request, Order $order)
    {
        if ($order->company_id !== Auth::user()->company_id) { abort(403); }
        
        $data = $request->validate([
            'service_id' => 'nullable|exists:services,id',
            'quote_notes' => 'nullable|string',
            'quote_delivery_start' => 'nullable|date',
            'quote_delivery_end' => 'nullable|date',
            'carrier_costs' => 'nullable|array',
            'carrier_costs.*.type' => 'nullable|string',
            'carrier_costs.*.description' => 'nullable|string',
            'carrier_costs.*.cost' => 'nullable|numeric',
            'carrier_costs.*.percentage' => 'nullable|numeric',
            'customer_quotes' => 'nullable|array',
            'customer_quotes.*.type' => 'nullable|string',
            'customer_quotes.*.description' => 'nullable|string',
            'customer_quotes.*.cost' => 'nullable|numeric',
            'customer_quotes.*.percentage' => 'nullable|numeric',
        ]);

        $this->orderService->saveQuote($order, $data);

        return redirect()->route('orders.edit', $order)->with('success', 'Quote saved successfully!');
    }

    /**
     * Sync Order to QuickBooks (Create Invoice)
     */
    public function syncToQuickBooks(Order $order)
    {
        if ($order->company_id !== Auth::user()->company_id) { abort(403); }

        try {
            // Check if QuickBooks plugin is active
            $plugin = \App\Models\Plugin::where('slug', 'quickbooks')->first();
            if (!$plugin || !$plugin->is_active) {
                return redirect()->back()->with('error', 'QuickBooks plugin is not active.');
            }

            // Get active configuration for this company
            $config = \App\Models\PluginConfiguration::where('plugin_slug', 'quickbooks')
                ->where('company_id', Auth::user()->company_id)
                ->where('is_active', true)
                ->first();

            if (!$config) {
                return redirect()->back()->with('error', 'No active QuickBooks configuration found. Please configure the plugin first.');
            }

            // Instantiate API Client with configuration (include ID for auto-refresh)
            $configArray = $config->configuration;
            $configArray['config_id'] = $config->id; // Add config ID for database updates during token refresh
            
            $apiClient = new \App\Plugins\QuickBooks\Services\ApiClient($configArray);
            $qbService = new \App\Plugins\QuickBooks\Services\QuickBooksService($apiClient);

            // Load order with relationships needed
            $order->load('customer', 'quote.costs');

            $invoiceResponse = $qbService->createInvoice($order);
            
            // Save the QuickBooks Invoice ID to the order
            $invoiceId = $invoiceResponse['Id'] ?? null;
            if ($invoiceId) {
                $order->update(['quickbooks_invoice_id' => $invoiceId]);
            }

            return redirect()->back()->with('success', "Invoice created in QuickBooks successfully! Invoice ID: {$invoiceId}");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to sync to QuickBooks: ' . $e->getMessage());
        }
    }
}