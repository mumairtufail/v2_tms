<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Order;
use App\Models\Customer;
use App\Services\OrderService;
use App\Services\PluginService;
use App\Plugins\QuickBooks\Services\QuickBooksService;
use App\Plugins\QuickBooks\Services\ApiClient;
use App\Support\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        // Create manifests map
        $manifests = \App\Models\Manifest::where('company_id', $company->id)->get();
        
        // If query param 'type' is present, temporarily override order type for view
        // Ideally, if it's a draft, we might want to save it, but display override is sufficient for the UI tab switch
        if (request()->has('type')) {
            $order->order_type = request('type');
        }

        // Prepare data for Alpine.js Form
        $stopsData = $order->stops->map(function($stop) {
            $consignee = $stop->consignee_data ?? [];
            $billing = $stop->billing_data ?? [];
            
            // Default consignee structure with all fields
            $consigneeDefaults = [
                'company_name' => '', 'address_1' => '', 'address_2' => '', 'city' => '', 'state' => '', 'zip' => '', 'country' => 'US',
                'contact_name' => '', 'phone' => '', 'email' => '', 'opening_time' => '08:00', 'closing_time' => '17:00',
                'ready_date' => '', 'ready_time' => '', 'appointment' => false, 'notes' => ''
            ];
            
            return [
                'uid' => uniqid(),
                'expanded' => false,
                'manifest_id' => $stop->manifest_id ?? '',
                'service_type' => $stop->service_type ?? 'truckload',
                'measurements' => $stop->measurement_type ?? 'in_lbs',
                'shipper' => [
                    'company_name' => $stop->company_name ?? '',
                    'address_1' => $stop->address_1 ?? '',
                    'address_2' => $stop->address_2 ?? '',
                    'city' => $stop->city ?? '',
                    'state' => $stop->state ?? '',
                    'zip' => $stop->postal_code ?? '',
                    'country' => $stop->country ?? 'US',
                    'contact_name' => $stop->contact_name ?? '',
                    'phone' => $stop->contact_phone ?? '',
                    'email' => $stop->contact_email ?? '',
                    'opening_time' => $stop->opening_time ?? '08:00',
                    'closing_time' => $stop->closing_time ?? '17:00',
                    'ready_date' => $stop->start_time ? date('Y-m-d', strtotime($stop->start_time)) : '',
                    'ready_time' => $stop->start_time ? date('H:i', strtotime($stop->start_time)) : '',
                    'appointment' => (bool)$stop->is_appointment,
                    'notes' => $stop->notes ?? '',
                ],
                'consignee' => array_merge($consigneeDefaults, $consignee),
                'billing' => array_merge([
                    'customs_broker' => '',
                    'port_of_entry' => '',
                    'container_number' => '',
                    'declared_value' => 0,
                    'currency' => 'USD',
                    'ref_number' => '',
                    'customer_po_number' => ''
                ], $billing),
                'commodities' => $stop->commodities->count() > 0 ? $stop->commodities->map(fn($c) => [
                    'description' => $c->description,
                    'qty' => $c->quantity,
                    'type' => $c->type ?? 'skid',
                    'weight' => (float)$c->weight,
                    'length' => (float)$c->length,
                    'width' => (float)$c->width,
                    'height' => (float)$c->height,
                    'pcs' => (int)$c->pieces,
                    'lf' => (float)$c->linear_feet,
                    'cube' => (float)$c->cube,
                    'freight_class' => $c->freight_class ?? '',
                ])->toArray() : [[
                    'description' => '',
                    'qty' => 1,
                    'type' => 'skid',
                    'weight' => 0,
                    'length' => 0,
                    'width' => 0,
                    'height' => 0,
                    'pcs' => 0,
                    'lf' => 0,
                    'cube' => 0,
                    'freight_class' => '',
                ]],
                'accessorials' => $stop->accessorials->pluck('id')->map(fn($id) => (string)$id)->toArray(),
            ];
        });

        // Quote Data
        $quote = $order->quote ?? new \App\Models\OrderQuote();
        $quoteData = [
            'service_id' => $quote->service_id ?? '',
            'delivery_start' => $quote->delivery_start_date ? date('Y-m-d', strtotime($quote->delivery_start_date)) : '',
            'delivery_end' => $quote->delivery_end_date ? date('Y-m-d', strtotime($quote->delivery_end_date)) : '',
            'customer_rows' => $quote->costs->where('category', 'customer')->map(fn($c) => [
                'type' => $c->type ?? 'Freight',
                'description' => $c->description ?? '',
                'cost' => $c->cost ?? 0
            ])->values()->toArray(),
            'carrier_rows' => $quote->costs->where('category', 'carrier')->map(fn($c) => [
                'type' => $c->type ?? 'Freight', // Default to Freight if null
                'description' => $c->description ?? '',
                'cost' => $c->cost ?? 0
            ])->values()->toArray(),
        ];

        $manifestsMap = $manifests->pluck('code', 'id')->toArray();

        // Default empty row if none exist
        if (empty($quoteData['customer_rows'])) {
            $quoteData['customer_rows'][] = ['type' => 'Freight', 'description' => '', 'cost' => 0];
        }
        // Carrier rows can be empty initially

        return view('v2.company.orders.form', compact('company', 'order', 'services', 'stopsData', 'allAccessorials', 'manifests', 'quoteData', 'manifestsMap'));
    }

    /**
     * Update the order.
     */
    public function update(Request $request, Company $company, Order $order)
    {
        $ordersLog = \Log::channel('orders');
        $ordersLog->info("=== Order Update Started ===", [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'user_id' => Auth::id(),
            'order_type' => $request->input('order_type'),
        ]);
        
        try {
            // Parse JSON stops and quote data from form
            $stopsData = json_decode($request->input('stops', '[]'), true);
            $quoteData = json_decode($request->input('quote_data', '{}'), true);
            $saveAsDraft = $request->input('save_as_draft') === '1';
            
            $ordersLog->info("Parsed form data", [
                'stops_count' => count($stopsData ?? []),
                'has_quote_data' => !empty($quoteData),
                'save_as_draft' => $saveAsDraft,
            ]);

            // Validate basic fields
            $validated = $request->validate([
                'order_type' => 'required|string',
                'ref_number' => 'nullable|string|max:255',
                'customer_po_number' => 'nullable|string|max:255',
                'special_instructions' => 'nullable|string',
            ]);

            // Update order basic fields
            $order->update([
                'ref_number' => $validated['ref_number'] ?? $order->ref_number,
                'customer_po_number' => $validated['customer_po_number'] ?? $order->customer_po_number,
                'special_instructions' => $validated['special_instructions'] ?? $order->special_instructions,
                'status' => $saveAsDraft ? 'draft' : 'pending',
            ]);

            $ordersLog->info("Updated order basic fields", [
                'status' => $order->status,
                'ref_number' => $order->ref_number,
            ]);

            // Process stops
            if (!empty($stopsData)) {
                $this->processStops($order, $stopsData, $ordersLog);
            }

            // Process quote data
            if (!empty($quoteData)) {
                $this->processQuote($order, $quoteData, $ordersLog);
            }

            $ordersLog->info("=== Order Update Completed Successfully ===", [
                'order_id' => $order->id,
            ]);

            $message = $saveAsDraft ? 'Order saved as draft.' : 'Order updated successfully.';
            return redirect()
                ->route('v2.orders.edit', ['company' => $company->slug, 'order' => $order->id])
                ->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $ordersLog->warning("Order update validation failed", [
                'order_id' => $order->id,
                'errors' => $e->errors(),
            ]);
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            $ordersLog->error("Order update failed", [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to update order: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Process and save stops data.
     */
    protected function processStops(Order $order, array $stopsData, $log)
    {
        $log->info("Processing stops", ['count' => count($stopsData)]);
        
        // Get existing stop IDs
        $existingStopIds = $order->stops->pluck('id')->toArray();
        $processedStopIds = [];

        foreach ($stopsData as $index => $stopData) {
            $stopId = $stopData['id'] ?? null;
            
            // Prepare start/end times
            $startTime = null;
            if (!empty($stopData['shipper']['ready_date'])) {
                $time = !empty($stopData['shipper']['ready_time']) ? $stopData['shipper']['ready_time'] : '00:00';
                $startTime = $stopData['shipper']['ready_date'] . ' ' . $time;
            }

            $endTime = null;
            if (!empty($stopData['consignee']['ready_date'])) {
                $time = !empty($stopData['consignee']['ready_time']) ? $stopData['consignee']['ready_time'] : '23:59';
                $endTime = $stopData['consignee']['ready_date'] . ' ' . $time;
            }

            // Prepare stop data
            $stopFields = [
                'order_id' => $order->id,
                'stop_type' => 'mixed',
                'sequence_number' => $index + 1,
                'service_type' => $stopData['service_type'] ?? 'truckload',
                'measurement_type' => $stopData['measurements'] ?? 'in_lbs',
                'manifest_id' => !empty($stopData['manifest_id']) ? $stopData['manifest_id'] : null,
                'company_name' => $stopData['shipper']['company_name'] ?? '',
                'address_1' => $stopData['shipper']['address_1'] ?? '',
                'address_2' => $stopData['shipper']['address_2'] ?? '',
                'city' => $stopData['shipper']['city'] ?? '',
                'state' => $stopData['shipper']['state'] ?? '',
                'postal_code' => $stopData['shipper']['zip'] ?? '',
                'country' => $stopData['shipper']['country'] ?? 'US',
                'contact_name' => $stopData['shipper']['contact_name'] ?? '',
                'contact_phone' => $stopData['shipper']['phone'] ?? '',
                'contact_email' => $stopData['shipper']['email'] ?? '',
                'opening_time' => $stopData['shipper']['opening_time'] ?? null,
                'closing_time' => $stopData['shipper']['closing_time'] ?? null,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_appointment' => (bool)($stopData['shipper']['appointment'] ?? false),
                'notes' => $stopData['shipper']['notes'] ?? '',
                'consignee_data' => $stopData['consignee'] ?? [],
                'billing_data' => $stopData['billing'] ?? [],
            ];

            // Create or update stop
            if ($stopId && in_array($stopId, $existingStopIds)) {
                $stop = \App\Models\OrderStop::find($stopId);
                $stop->update($stopFields);
                $processedStopIds[] = $stopId;
                $log->info("Updated stop", ['stop_id' => $stopId, 'sequence' => $index + 1]);
            } else {
                $stop = \App\Models\OrderStop::create($stopFields);
                $processedStopIds[] = $stop->id;
                $log->info("Created stop", ['stop_id' => $stop->id, 'sequence' => $index + 1]);
            }

            // Process commodities
            $this->processCommodities($stop, $stopData['commodities'] ?? [], $log);
            
            // Process accessorials
            $accessorialIds = array_map('intval', $stopData['accessorials'] ?? []);
            $stop->accessorials()->sync($accessorialIds);
            $log->info("Synced accessorials", ['stop_id' => $stop->id, 'count' => count($accessorialIds)]);
        }

        // Delete stops that were removed
        $stopsToDelete = array_diff($existingStopIds, $processedStopIds);
        if (!empty($stopsToDelete)) {
            \App\Models\OrderStop::whereIn('id', $stopsToDelete)->delete();
            $log->info("Deleted stops", ['count' => count($stopsToDelete)]);
        }
    }

    /**
     * Process and save commodities for a stop.
     */
    protected function processCommodities(\App\Models\OrderStop $stop, array $commoditiesData, $log)
    {
        // Delete existing commodities and recreate
        $stop->commodities()->delete();
        
        foreach ($commoditiesData as $comData) {
            \App\Models\OrderStopCommodity::create([
                'order_stop_id' => $stop->id,
                'description' => $comData['description'] ?? '',
                'type' => $comData['type'] ?? 'skid',
                'quantity' => $comData['qty'] ?? 1,
                'pieces' => $comData['pcs'] ?? 0,
                'weight' => $comData['weight'] ?? 0,
                'length' => $comData['length'] ?? null,
                'width' => $comData['width'] ?? null,
                'height' => $comData['height'] ?? null,
                'linear_feet' => $comData['lf'] ?? null,
                'cube' => $comData['cube'] ?? null,
                'freight_class' => $comData['freight_class'] ?? null,
                'measurement_type' => $stopData['measurements'] ?? 'imperial',
            ]);
        }
        
        $log->info("Recreated commodities", ['stop_id' => $stop->id, 'count' => count($commoditiesData)]);
    }

    /**
     * Process and save quote data.
     */
    protected function processQuote(Order $order, array $quoteData, $log)
    {
        $quote = $order->quote ?? new \App\Models\OrderQuote(['order_id' => $order->id]);
        
        $quote->fill([
            'service_id' => $quoteData['service_id'] ?: null,
            'delivery_start_date' => $quoteData['delivery_start'] ?: null,
            'delivery_end_date' => $quoteData['delivery_end'] ?: null,
        ]);
        $quote->save();

        // Clear existing costs
        $quote->costs()->delete();

        // Add customer rows (Revenue)
        foreach ($quoteData['customer_rows'] ?? [] as $row) {
            if (!empty($row['description']) || (!empty($row['cost']) && $row['cost'] != 0)) {
                $quote->costs()->create([
                    'category' => 'customer',
                    'type' => $row['type'] ?? 'Freight',
                    'description' => $row['description'] ?? '',
                    'cost' => $row['cost'] ?? 0,
                ]);
            }
        }

        // Add carrier rows (Costs)
        foreach ($quoteData['carrier_rows'] ?? [] as $row) {
            if (!empty($row['description']) || (!empty($row['cost']) && $row['cost'] != 0)) {
                $quote->costs()->create([
                    'category' => 'carrier',
                    'type' => $row['type'] ?? 'Freight',
                    'description' => $row['description'] ?? '',
                    'cost' => $row['cost'] ?? 0,
                ]);
            }
        }

        $log->info("Processed quote", [
            'quote_id' => $quote->id,
            'service_id' => $quote->service_id,
            'customer_rows' => count($quoteData['customer_rows'] ?? []),
            'carrier_rows' => count($quoteData['carrier_rows'] ?? []),
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
