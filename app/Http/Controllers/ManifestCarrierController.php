<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Manifest;
use App\Models\ManifestCarrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManifestCarrierController extends Controller
{
    /**
     * Get all carriers with assignment status for a manifest
     * 
     * @param int $manifestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCarriers($manifestId)
    {
        try {
            // Get all carriers
            $carriers = Carrier::where('is_active', 1)->get();
            
            // Get IDs of carriers already assigned to this manifest
            $assignedIds = ManifestCarrier::where('manifest_id', $manifestId)
                ->pluck('carrier_id')
                ->toArray();
                
            // Add a flag to indicate if the carrier is assigned to this manifest
            $carriers = $carriers->map(function($item) use ($assignedIds) {
                $item->is_assigned = in_array($item->id, $assignedIds);
                return $item;
            });
            
            return response()->json([
                'success' => true,
                'carriers' => $carriers
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching carriers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load carriers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign carriers to a manifest
     * 
     * @param Request $request
     * @param int $manifestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignCarriers(Request $request, $manifestId)
    {
        try {
            $validatedData = $request->validate([
                'carrier_ids' => 'required|array',
                'carrier_ids.*' => 'integer|exists:carriers,id'
            ]);

            $manifest = Manifest::findOrFail($manifestId);
            
            // Begin transaction
            DB::beginTransaction();
            
            // Remove all current carrier assignments for this manifest
            ManifestCarrier::where('manifest_id', $manifestId)->delete();
            
            // Add new carrier assignments
            $carrierIds = $validatedData['carrier_ids'];
            $insertData = [];
            
            foreach ($carrierIds as $carrierId) {
                $insertData[] = [
                    'manifest_id' => $manifestId,
                    'carrier_id' => $carrierId,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            if (!empty($insertData)) {
                ManifestCarrier::insert($insertData);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Carriers assigned successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning carriers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign carriers: ' . $e->getMessage()
            ], 500);
        }
    }
}