<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * View customers API endpoint
     */
    public function viewcustomers($customer)
    {
        $companyId = auth()->user()->company_id ?? null;
        if (!$companyId) {
            return response()->json(['success' => false, 'message' => 'Customer not found'], 404);
        }

        try {
            $customerData = Customer::where('company_id', $companyId)->findOrFail($customer);

            return response()->json([
                'success' => true,
                'data' => $customerData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }
    }

    /**
     * Search customers API endpoint
     */
    public function search(Request $request)
    {
        try {
            $companyId = auth()->user()->company_id ?? null;
            if (!$companyId) {
                return response()->json([]);
            }

            $query = $request->get('q', '');

            $customers = Customer::query()->where('company_id', $companyId);

            if ($query) {
                  $customers->where(function($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('customer_email', 'LIKE', "%{$query}%")
                      ->orWhere('city', 'LIKE', "%{$query}%")
                      ->orWhere('state', 'LIKE', "%{$query}%")
                      ->orWhere('short_code', 'LIKE', "%{$query}%");
                });
            }

            $customers = $customers
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->orderBy('name')
                ->limit(50) // Increased limit for showing all by default
                ->get([
                    'id', 'name', 'customer_email', 'address', 'city', 
                    'state', 'postal_code', 'country', 'short_code', 'customer_type'
                ]);
            
            return response()->json($customers);
        } catch (\Exception $e) {
            \Log::error('Customer search error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to search customers',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
