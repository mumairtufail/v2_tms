<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\ActivityLog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    protected $activityLogService;

    public function __construct(ActivityLog $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $query = Customer::query()->notDeleted();

        // Filter by company - only show customers from user's company
        $query->where('company_id', auth()->user()->company_id);

        // Handle search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_code', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%");
            });
        }

        // Handle filters
        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Get page size from request or default to 15
        $perPage = $request->get('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100]) ? $perPage : 15;
        
        $customers = $query->orderBy('name')->paginate($perPage);
        
        // Append query parameters to pagination links
        $customers->appends($request->query());

        return view('dashboard.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('dashboard.customers.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'short_code' => 'nullable|string|max:255',
            'portal' => 'boolean',
            'location_sharing' => 'required|in:Do not share,approximate,exact live location',
            'network_customer' => 'boolean',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'customer_email' => 'required|string|email|max:255|unique:customers',
            'postal_code' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:255',
            'customer_type' => 'required|in:shipper,broker,carrier,other',
            'default_billing_option' => 'required|in:third_party,consignee,shipper',
            'quote_required' => 'boolean'
        ]);

        if ($validator->fails()) {
            $errorMessage = implode(', ', $validator->errors()->all());
            return back()->with('error', 'Failed to store .Please try again. ' . $errorMessage);
        }
        $validated = $validator->validated();
        
        // Automatically set company_id from authenticated user
        $validated['company_id'] = auth()->user()->company_id;
        
        try {
            $customer = Customer::create($validated);
            
            // QuickBooks Integration
            try {
                $qbConfig = \App\Models\PluginConfiguration::where('company_id', auth()->user()->company_id)
                    ->where('plugin_slug', 'quickbooks')
                    ->where('is_active', true)
                    ->first();

                if ($qbConfig) {
                    // Debug logging
                    Log::channel('plugins')->info('QuickBooks Config Found', [
                        'id' => $qbConfig->id,
                        'keys' => array_keys($qbConfig->configuration ?? []),
                        'has_access_token' => !empty($qbConfig->configuration['access_token']),
                        'has_realm_id' => !empty($qbConfig->configuration['realm_id'])
                    ]);

                    $configData = $qbConfig->configuration;
                    $configData['config_id'] = $qbConfig->id; // Inject ID for token refresh updates

                    $apiClient = new \App\Plugins\QuickBooks\Services\ApiClient($configData);
                    $qbService = new \App\Plugins\QuickBooks\Services\QuickBooksService($apiClient);
                    
                    $qbCustomer = $qbService->createCustomer($customer->toArray());
                    
                    if ($qbCustomer && isset($qbCustomer['Id'])) {
                        $customer->update(['quickbooks_id' => $qbCustomer['Id']]);
                    }
                } else {
                    Log::channel('plugins')->info('No active QuickBooks configuration found for company ' . auth()->user()->company_id);
                }
            } catch (Exception $e) {
                // Log the error but don't fail the request, just notify
                Log::channel('plugins')->error('QuickBooks Sync Failed: ' . $e->getMessage());
                // Optionally append warning to success message
            }

            $action = $request->name . ' customer created';
            $additionalData = [
                'is_successful' => true,
                'data' => $customer->toArray(), 
            ];
            $this->activityLogService->log($action, $additionalData);

            return redirect()->route('customers.index')->with('success', 'Customer created successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error occured' . $e->getMessage());
        }
    }

    public function show(Customer $customer)
    {
        // Ensure user can only view customers from their company
        if ($customer->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access to customer.');
        }
        
        return view('dashboard.customers.show', compact('customer'));
    }

    public function edit(Customer $customer, $id)
    {
        // Find customer and ensure it belongs to user's company
        $customer = Customer::where('id', $id)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();
            
        return view('dashboard.customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        // Find customer and ensure it belongs to user's company
        $customer = Customer::where('id', $id)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'short_code' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'customer_email' => 'required|string|email|max:255|unique:customers,customer_email,' . $id,
            'portal' => 'boolean',
            'location_sharing' => 'required|in:Do not share,approximate,exact live location',
            'network_customer' => 'boolean',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:255',
            'customer_type' => 'required|in:shipper,broker,carrier,other',
            'default_billing_option' => 'required|in:third_party,consignee,shipper',
            'quote_required' => 'boolean'
        ]);
        if ($validator->fails()) {
            $errorMessage = implode(', ', $validator->errors()->all());
            return back()->with('error', 'Failed to store .Please try again. ' . $errorMessage);
        }
        try {
            $validated = $validator->validated();
            
            // Ensure company_id cannot be changed
            unset($validated['company_id']);
            
            $customer->update($validated);

            $action = $request->name . ' customer updated';
            $additionalData = [
                'is_successful' => true,
                'data' => $customer->toArray(), 
            ];
            $this->activityLogService->log($action, $additionalData);


            return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error occured' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        // Find customer and ensure it belongs to user's company
        $customer = Customer::where('id', $id)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();
            
        $customer->update(['is_deleted' => true]);

        $action = $customer->name . ' customer deleted';
        $this->activityLogService->log($action);

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    public function viewcustomers(Customer $customer)
    {
        if (!Auth::user()->is_admin && Auth::user()->company_id != $customer->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
       
        
        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'address' => $customer->address,
            'city' => $customer->city,
            'state' => $customer->state,
            'postal_code' => $customer->postal_code,
            'country' => $customer->country,
            'currency' => $customer->currency,
            'contact_name' => $customer->contact_name,
            'contact_email' => $customer->contact_email,
            'contact_phone' => $customer->contact_phone,
        ]);
    }

    /**
     * Search customers for order creation
     */
    public function searchCustomers(Request $request)
    {
        try {
            $query = $request->input('query', '');
            
            // Search customers with LIKE queries on relevant fields
            $customers = Customer::where('is_active', true)
                ->where('is_deleted', false)
                ->where('company_id', auth()->user()->company_id)
                ->where(function ($queryBuilder) use ($query) {
                    $queryBuilder->where('name', 'LIKE', "%{$query}%")
                               ->orWhere('customer_email', 'LIKE', "%{$query}%")
                               ->orWhere('short_code', 'LIKE', "%{$query}%")
                               ->orWhere('city', 'LIKE', "%{$query}%")
                               ->orWhere('address', 'LIKE', "%{$query}%");
                })
                ->select(['id', 'name as company_name', 'customer_email as email', 'short_code', 'city', 'address', 'customer_type'])
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'customers' => $customers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching customers: ' . $e->getMessage()
            ], 500);
        }
    }
}
