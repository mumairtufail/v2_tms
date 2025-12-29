<?php

namespace App\Services;

use App\Models\Manifest;
use App\Models\ManifestDriver;
use App\Models\ManifestCarrier;
use App\Models\ManifestEquipment;
use App\Models\Stop;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ManifestService
{
    public function getManifests(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Manifest::where('company_id', $companyId)
            ->with(['drivers', 'carriers', 'equipments', 'stops']);

        if (!empty($filters['search'])) {
            $query->where('code', 'like', '%' . $filters['search'] . '%');
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function createManifest(int $companyId, array $data): Manifest
    {
        return DB::transaction(function () use ($companyId, $data) {
            $data['company_id'] = $companyId;
            if (empty($data['code'])) {
                $data['code'] = 'MAN-' . strtoupper(uniqid());
            }
            return Manifest::create($data);
        });
    }

    public function updateManifest(Manifest $manifest, array $data): Manifest
    {
        return DB::transaction(function () use ($manifest, $data) {
            // Update basic manifest fields
            $manifest->update($data);

            // Handle Cost Estimates
            if (isset($data['cost_estimates'])) {
                // Remove existing estimates to replace with new set
                $manifest->costEstimates()->delete();
                
                foreach ($data['cost_estimates'] as $estimate) {
                    // Skip empty rows if any
                    if (empty($estimate['type']) && empty($estimate['description'])) {
                        continue;
                    }

                    // Calculate total cost
                    $qty = floatval($estimate['qty'] ?? 0);
                    $rate = floatval($estimate['rate'] ?? 0);
                    $estimate['est_cost'] = $qty * $rate;
                    
                    $manifest->costEstimates()->create($estimate);
                }
            }
            
            return $manifest;
        });
    }

    public function deleteManifest(Manifest $manifest): void
    {
        DB::transaction(function () use ($manifest) {
            $manifest->manifestDrivers()->delete();
            $manifest->manifestEquipment()->delete();
            // manifestCarriers relationship is belongsToMany, so detach
            $manifest->carriers()->detach();
            $manifest->stops()->delete();
            $manifest->delete();
        });
    }

    public function assignDriver(Manifest $manifest, int $driverId): void
    {
        ManifestDriver::firstOrCreate([
            'manifest_id' => $manifest->id,
            'driver_id' => $driverId
        ]);
    }

    public function removeDriver(Manifest $manifest, int $driverId): void
    {
        ManifestDriver::where('manifest_id', $manifest->id)
            ->where('driver_id', $driverId)
            ->delete();
    }

    public function assignCarrier(Manifest $manifest, int $carrierId): void
    {
        ManifestCarrier::firstOrCreate([
            'manifest_id' => $manifest->id,
            'carrier_id' => $carrierId
        ]);
    }

    public function removeCarrier(Manifest $manifest, int $carrierId): void
    {
        ManifestCarrier::where('manifest_id', $manifest->id)
            ->where('carrier_id', $carrierId)
            ->delete();
    }

    public function assignEquipment(Manifest $manifest, int $equipmentId): void
    {
        ManifestEquipment::firstOrCreate([
            'manifest_id' => $manifest->id,
            'equipment_id' => $equipmentId
        ]);
    }

    public function removeEquipment(Manifest $manifest, int $equipmentId): void
    {
        ManifestEquipment::where('manifest_id', $manifest->id)
            ->where('equipment_id', $equipmentId)
            ->delete();
    }

    public function addStop(Manifest $manifest, array $data): Stop
    {
        $data['manifest_id'] = $manifest->id;
        return Stop::create($data);
    }

    public function removeStop(int $stopId): void
    {
        Stop::destroy($stopId);
    }
}
