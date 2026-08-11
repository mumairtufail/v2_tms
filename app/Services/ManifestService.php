<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Manifest;
use App\Models\ManifestDriver;
use App\Models\ManifestCarrier;
use App\Models\ManifestEquipment;
use App\Models\OrderQuote;
use App\Models\Stop;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class ManifestService
{
    public function getManifests(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Manifest::where('company_id', $companyId)
            ->with(['drivers', 'carriers', 'equipments', 'stops', 'orders']);

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
            
            // Generate a temporary unique placeholder if code is missing, to satisfy unique constraint
            $isAutoCode = empty($data['code']);
            if ($isAutoCode) {
                 $data['code'] = 'TEMP-' . uniqid();
            }
            
            $manifest = Manifest::create($data);

            // Update with shortcode-based manifest code: {SC}-M-{zero-padded-id}
            // e.g. INVO-M-0013 — always unique because id is unique
            if ($isAutoCode) {
                $company = Company::find($companyId);
                $sc = strtoupper($company?->shortcode ?: Str::upper(Str::substr($company?->name ?? 'MAN', 0, 4)));
                $manifest->update([
                    'code' => $sc . '-M-' . str_pad($manifest->id, 4, '0', STR_PAD_LEFT),
                ]);
            }
            
            return $manifest;
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

                $freightSubtotal = $this->extractFreightSubtotal($data['cost_estimates']);

                foreach ($data['cost_estimates'] as $estimate) {
                    if (empty($estimate['type']) && empty($estimate['description'])) {
                        continue;
                    }

                    $type        = (string) ($estimate['type'] ?? 'Miscellaneous');
                    $typeLower   = strtolower($type);
                    $description = (string) ($estimate['description'] ?? '');
                    $qty         = (float) ($estimate['qty'] ?? 0);
                    $rate        = (float) ($estimate['rate'] ?? 0);

                    if ($typeLower === 'fuel (surcharge)') {
                        $percentage     = max(0, min(100, $qty));
                        $calculatedCost = round($freightSubtotal * ($percentage / 100), 2);

                        $manifest->costEstimates()->create([
                            'type'        => $type,
                            'description' => $description ?: "Fuel Surcharge ({$percentage}%)",
                            'qty'         => $percentage,
                            'rate'        => $freightSubtotal,
                            'est_cost'    => $calculatedCost,
                        ]);
                        continue;
                    }

                    $estCost = round($qty * $rate, 2);
                    if ($estCost <= 0 && $description === '') {
                        continue;
                    }

                    $manifest->costEstimates()->create([
                        'type'        => $type,
                        'description' => $description,
                        'qty'         => $qty,
                        'rate'        => $rate,
                        'est_cost'    => $estCost,
                    ]);
                }

                $this->syncOrderQuotesFromManifestCosts($manifest);
            }
            
            return $manifest;
        });
    }

    public function syncOrderQuotesFromManifestCosts(Manifest $manifest): void
    {
        $manifest->loadMissing('costEstimates', 'orderStops.order.quote.costs');

        $carrierRows = $manifest->costEstimates->map(function ($estimate) {
            $type       = (string) ($estimate->type ?? 'Miscellaneous');
            $typeLower  = strtolower($type);
            $percentage = $typeLower === 'fuel (surcharge)'
                ? max(0, min(100, (float) ($estimate->qty ?? 0)))
                : null;

            return [
                'category' => 'carrier',
                'type' => $type,
                'description' => (string) ($estimate->description ?? ''),
                'cost' => (float) ($estimate->est_cost ?? 0),
                'percentage' => $percentage,
            ];
        })->values();

        $orders = $manifest->orderStops
            ->pluck('order')
            ->filter()
            ->unique('id')
            ->values();

        foreach ($orders as $order) {
            $quote = $order->quote;
            if (!$quote) {
                $quote = OrderQuote::create([
                    'order_id' => $order->id,
                    'service_id' => null,
                    'notes' => null,
                    'delivery_start_date' => null,
                    'delivery_end_date' => null,
                ]);
            }

            $quote->costs()->where('category', 'carrier')->delete();

            foreach ($carrierRows as $row) {
                $quote->costs()->create($row);
            }
        }
    }

    private function extractFreightSubtotal(array $estimates): float
    {
        return collect($estimates)
            ->filter(function ($estimate) {
                $t = strtolower((string) ($estimate['type'] ?? ''));
                return $t === 'freight' || $t === 'freight (per mile)';
            })
            ->sum(function ($estimate) {
                return (float) ($estimate['qty'] ?? 0) * (float) ($estimate['rate'] ?? 0);
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
            'equipment_id' => $equipmentId,
        ]);

        \App\Models\Equipment::where('id', $equipmentId)->update(['status' => 'In Use']);
    }

    public function removeEquipment(Manifest $manifest, int $equipmentId): void
    {
        ManifestEquipment::where('manifest_id', $manifest->id)
            ->where('equipment_id', $equipmentId)
            ->delete();

        $stillAssigned = ManifestEquipment::where('equipment_id', $equipmentId)->exists();

        if (! $stillAssigned) {
            \App\Models\Equipment::where('id', $equipmentId)->update(['status' => 'Available']);
        }
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
