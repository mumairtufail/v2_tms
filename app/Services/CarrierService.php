<?php

namespace App\Services;

use App\Models\Carrier;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CarrierService
{
    public function getCarriers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Carrier::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where('carrier_name', 'like', "%{$search}%")
                      ->orWhere('dot_id', 'like', "%{$search}%")
                      ->orWhere('docket_number', 'like', "%{$search}%");
            })
            ->when(isset($filters['status']), function ($query) use ($filters) {
                if ($filters['status'] === 'active') {
                    $query->where('is_active', true);
                } elseif ($filters['status'] === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->latest()
            ->paginate($perPage);
    }

    public function createCarrier(array $data): Carrier
    {
        return DB::transaction(function () use ($data) {
            return Carrier::create($data);
        });
    }

    public function updateCarrier(Carrier $carrier, array $data): Carrier
    {
        return DB::transaction(function () use ($carrier, $data) {
            $carrier->update($data);
            return $carrier->fresh();
        });
    }

    public function deleteCarrier(Carrier $carrier): bool
    {
        return DB::transaction(function () use ($carrier) {
            return $carrier->delete();
        });
    }
}
