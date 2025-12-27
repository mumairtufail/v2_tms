<?php

namespace App\Http\Controllers;

use App\Models\CompanyUser;
use App\Models\Manifest;
use App\Models\ManifestDriver;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManifestDriverController extends Controller
{
    /**
     * Get all drivers with assignment status for a specific manifest
     */
    public function getDrivers($manifestId)
    {
        $companyId = Auth::user()->company_id;
        $drivers = User::where('company_id', $companyId)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'driver');
            })
            ->where('is_deleted', 0)
            ->get();
        // Get IDs of drivers already assigned to this manifest
        $assignedIds = ManifestDriver::where('manifest_id', $manifestId)
            ->pluck('driver_id')
            ->toArray();

        // Add a flag to indicate if the driver is assigned to this manifest
        $drivers = $drivers->map(function ($item) use ($assignedIds) {
            $item->is_assigned = in_array($item->id, $assignedIds);
            return $item;
        });

        return response()->json([
            'success' => true,
            'drivers' => $drivers
        ]);
    }

    /**
     * Assign drivers to a manifest
     */
    public function assignDrivers(Request $request, $manifestId)
    {
        // Debug logging
        \Log::info('Assign drivers request', [
            'manifest_id' => $manifestId,
            'driver_ids' => $request->driver_ids,
            'request_data' => $request->all()
        ]);

        $request->validate([
            'driver_ids' => 'nullable|array',
            'driver_ids.*' => 'exists:users,id',
        ]);

        DB::beginTransaction();

        try {
            // Delete existing driver assignments
            ManifestDriver::where('manifest_id', $manifestId)->delete();

            // Create new assignments
            foreach ($request->driver_ids as $driverId) {
                ManifestDriver::create([
                    'manifest_id' => $manifestId,
                    'driver_id' => $driverId,
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
                'message' => 'Drivers assigned successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign drivers: ' . $e->getMessage(),
            ], 500);
        }
    }
}
