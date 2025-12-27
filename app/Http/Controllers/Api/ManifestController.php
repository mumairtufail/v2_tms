<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Manifest;
use Illuminate\Http\Request;

class ManifestController extends Controller
{
    /**
     * Get all manifests for dropdown selection
     */
    public function index()
    {
        try {
            // Now using web routes with proper authentication
            $user = auth()->user();
            \Log::info('Manifest API called', ['user_id' => $user->id, 'is_super_admin' => $user->is_super_admin, 'company_id' => $user->company_id]);
            
            $manifests = Manifest::query()
                ->with(['drivers', 'carriers']);
            
            // If user is not super admin, filter by their company
            if (!$user->is_super_admin) {
                $manifests->where('company_id', $user->company_id);
            }
            
            $manifestsResult = $manifests // Only show non-draft manifests
                ->orderBy('code', 'asc')
                ->get();
                
            \Log::info('Manifest query result', ['count' => $manifestsResult->count()]);
                
            $manifestsData = $manifestsResult->map(function($manifest) {
                return [
                    'id' => $manifest->id,
                    'manifest_number' => $manifest->code,
                    'customer_name' => $manifest->drivers->first()->name ?? 'No driver assigned',
                    'status' => $manifest->status ?? 'Active',
                    'created_at' => $manifest->created_at->format('M j, Y')
                ];
            });
            
            \Log::info('Manifest API response', ['manifests' => $manifestsData->toArray()]);
            
            return response()->json($manifestsData);
        } catch (\Exception $e) {
            \Log::error('Manifest index error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load manifests',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search manifests API endpoint
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $user = auth()->user();
            
            $manifests = Manifest::query()
                ->with(['drivers', 'carriers', 'equipments'])
                ->withCount('orders');
            
            // If user is not super admin, filter by their company
            if (!$user->is_super_admin) {
                $manifests->where('company_id', $user->company_id);
            }
                
            if ($query) {
                $manifests->where(function($q) use ($query) {
                    $q->where('code', 'LIKE', "%{$query}%")
                      ->orWhereHas('drivers', function($q) use ($query) {
                          $q->where('name', 'LIKE', "%{$query}%");
                      })
                      ->orWhereHas('carriers', function($q) use ($query) {
                          $q->where('name', 'LIKE', "%{$query}%");
                      })
                      ->orWhereHas('equipments', function($q) use ($query) {
                          $q->where('unit_number', 'LIKE', "%{$query}%");
                      });
                });
            }
            
            $manifests = $manifests
                ->where('draft', false) // Only show non-draft manifests
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function($manifest) {
                    // Add computed fields for easier frontend access
                    $manifest->manifest_number = $manifest->code;
                    $manifest->driver = $manifest->drivers->first(); // Get first driver
                    $manifest->carrier = $manifest->carriers->first(); // Get first carrier
                    $manifest->equipment = $manifest->equipments->first(); // Get first equipment
                    return $manifest;
                });
            
            return response()->json($manifests);
        } catch (\Exception $e) {
            \Log::error('Manifest search error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to search manifests',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}