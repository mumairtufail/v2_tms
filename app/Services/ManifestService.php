<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Manifest;
use App\Models\ManifestDriver;
use App\Models\ManifestCarrier;
use App\Models\ManifestEquipment;
use App\Models\Order;
use App\Models\OrderStop;
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

            // Handle Cost Estimates — same writer the order screen uses, so both agree
            if (isset($data['cost_estimates'])) {
                $this->replaceCostEstimates($manifest, $data['cost_estimates']);
            }

            return $manifest;
        });
    }

    /**
     * Replace a manifest's cost estimates with the given rows.
     *
     * This is the one place carrier cost is stored, whether it was typed on the manifest
     * or on an order whose stops all sit on this manifest.
     */
    public function replaceCostEstimates(Manifest $manifest, array $rows): void
    {
        DB::transaction(function () use ($manifest, $rows) {
            $manifest->costEstimates()->delete();

            $freightSubtotal = $this->extractFreightSubtotal($rows);

            foreach ($rows as $estimate) {
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
        });

        $manifest->load('costEstimates');
    }

    /**
     * Financial roll-up for a manifest.
     *
     * Revenue is the customer side of the linked orders; costs are this manifest's own
     * line items. Carrier costs entered on an order belong to that order and are not
     * counted here.
     *
     * An order can be split across several manifests, and a manifest can carry stops
     * from several orders, so nothing is counted whole on either side:
     *  - the order's customer revenue is counted by the share of the order's stops that
     *    sit on this manifest (4 of 10 stops => 40%);
     *  - this manifest's own cost estimates are split across its orders by how many of
     *    the manifest's stops belong to each, so two manifests never double count.
     */
    public function financialSummary(Manifest $manifest): array
    {
        $manifest->loadMissing([
            'costEstimates',
            'orderStops.order.customer',
            'orderStops.order.quote.costs',
            'orderStops.order.stops',
        ]);

        $manifestCost   = round((float) $manifest->costEstimates->sum('est_cost'), 2);
        $stopsOnManifest = $manifest->orderStops->count();

        $orders = $manifest->orderStops
            ->pluck('order')
            ->filter()
            ->unique('id')
            ->values()
            ->map(function ($order) use ($manifest, $manifestCost, $stopsOnManifest) {
                $stopsHere  = $manifest->orderStops->where('order_id', $order->id)->count();
                $stopsTotal = $order->stops->count();

                // Share of the order that sits on this manifest
                $share = $stopsTotal > 0 ? $stopsHere / $stopsTotal : 0.0;
                // Share of this manifest's own costs that belongs to this order
                $costShare = $stopsOnManifest > 0 ? $stopsHere / $stopsOnManifest : 0.0;

                // Only the customer side: carrier costs belong to the order, not to this manifest
                $revenueFull = (float) ($order->quote?->costs ?? collect())
                    ->where('category', 'customer')
                    ->sum('cost');

                return [
                    'id'                  => $order->id,
                    'number'              => $order->order_number,
                    'customer'            => $order->customer->name ?? null,
                    'stops_on_manifest'   => $stopsHere,
                    'stops_total'         => $stopsTotal,
                    'is_partial'          => $stopsHere < $stopsTotal,
                    'share'               => round($share, 4),
                    'revenue_full'        => round($revenueFull, 2),
                    'revenue_share'       => round($revenueFull * $share, 2),
                    'manifest_cost_share' => round($manifestCost * $costShare, 2),
                ];
            });

        $revenue = round($orders->sum('revenue_share'), 2);

        return [
            'orders'        => $orders->all(),
            'revenue'       => $revenue,
            'manifest_cost' => $manifestCost,
            'costs'         => $manifestCost,
            'profit'        => round($revenue - $manifestCost, 2),
            'margin'        => $revenue > 0 ? round((($revenue - $manifestCost) / $revenue) * 100, 1) : null,
        ];
    }

    /**
     * What this order is charged for carrier cost, allocated from the manifests its stops
     * sit on. Carrier cost is entered on the manifest (that is what the rate confirmation
     * pays), so the order only ever reads its share back.
     *
     * A manifest's cost is split across the orders on it by stop count, so two orders
     * sharing one manifest never each carry the whole cost.
     */
    public function carrierCostForOrder(Order $order): array
    {
        $order->loadMissing('stops.manifest.costEstimates');

        $manifests = $order->stops->pluck('manifest')->filter()->unique('id')->values();

        // How many order stops each of those manifests carries in total, across all orders
        $stopsPerManifest = OrderStop::whereIn('manifest_id', $manifests->pluck('id'))
            ->selectRaw('manifest_id, COUNT(*) as total')
            ->groupBy('manifest_id')
            ->pluck('total', 'manifest_id');

        $rows = $manifests->map(function (Manifest $manifest) use ($order, $stopsPerManifest) {
            $stopsHere     = $order->stops->where('manifest_id', $manifest->id)->count();
            $stopsOnTrip   = (int) ($stopsPerManifest[$manifest->id] ?? 0);
            $share         = $stopsOnTrip > 0 ? $stopsHere / $stopsOnTrip : 0.0;
            $manifestCost  = (float) $manifest->costEstimates->sum('est_cost');

            return [
                'id'              => $manifest->id,
                'code'            => $manifest->code,
                'stops_here'      => $stopsHere,
                'stops_on_trip'   => $stopsOnTrip,
                'is_shared'       => $stopsHere < $stopsOnTrip,
                'share'           => round($share, 4),
                'manifest_cost'   => round($manifestCost, 2),
                'amount'          => round($manifestCost * $share, 2),
            ];
        })->sortBy('code')->values();

        return [
            'rows'  => $rows->all(),
            'total' => round($rows->sum('amount'), 2),
        ];
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
