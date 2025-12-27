<?php

namespace App\Services;

use App\Models\Equipment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EquipmentService
{
    public function getEquipment(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Equipment::query()
            ->when($filters['company_id'] ?? null, fn($q, $id) => $q->where('company_id', $id))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('type', 'like', "%{$search}%")
                      ->orWhere('sub_type', 'like', "%{$search}%");
            })
            ->when(isset($filters['status']), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->latest()
            ->paginate($perPage);
    }

    public function createEquipment(array $data): Equipment
    {
        return DB::transaction(function () use ($data) {
            return Equipment::create($data);
        });
    }

    public function updateEquipment(Equipment $equipment, array $data): Equipment
    {
        return DB::transaction(function () use ($equipment, $data) {
            $equipment->update($data);
            return $equipment->fresh();
        });
    }

    public function deleteEquipment(Equipment $equipment): bool
    {
        return DB::transaction(function () use ($equipment) {
            return $equipment->delete();
        });
    }
}
