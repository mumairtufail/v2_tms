<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Manifest;
use App\Models\ManifestEquipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManifestEquipmentController extends Controller
{
    /**
     * Get all equipment with status for a specific manifest
     */
    public function getEquipment($manifestId)
    {
        // Get all equipment for the company
        $equipment = Equipment::where('company_id', Auth::user()->company_id)->get();
        
        // Get IDs of equipment already assigned to this manifest
        $assignedIds = ManifestEquipment::where('manifest_id', $manifestId)
            ->pluck('equipment_id')
            ->toArray();
            
        // Add a flag to indicate if the equipment is assigned to this manifest
        $equipment = $equipment->map(function($item) use ($assignedIds) {
            $item->is_assigned = in_array($item->id, $assignedIds);
            return $item;
        });
        
        return response()->json([
            'success' => true,
            'equipment' => $equipment
        ]);
    }
    
    /**
     * Assign equipment to a manifest
     */
    public function assignEquipment(Request $request, $manifestId)
    {
        $request->validate([
            'equipment_ids' => 'required|array',
            'equipment_ids.*' => 'exists:equipment,id',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Delete existing equipment assignments
            ManifestEquipment::where('manifest_id', $manifestId)->delete();
            
            // Create new assignments
            foreach ($request->equipment_ids as $equipmentId) {
                ManifestEquipment::create([
                    'manifest_id' => $manifestId,
                    'equipment_id' => $equipmentId,
                ]);
            }
            
            // Update manifest draft status if needed
            $manifest = Manifest::find($manifestId);
            if ($manifest && $manifest->draft == 1) {
                $manifest->draft = 0;
                $manifest->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Equipment assigned successfully',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign equipment: ' . $e->getMessage(),
            ], 500);
        }
    }
}
