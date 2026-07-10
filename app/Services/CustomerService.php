<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function getCustomers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Customer::query()
            ->when($filters['company_id'] ?? null, fn($q, $id) => $q->where('company_id', $id))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('customer_email', 'like', "%{$search}%")
                      ->orWhere('short_code', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['status']), function ($query) use ($filters) {
                if ($filters['status'] === 'active') {
                    $query->where('is_active', true);
                } elseif ($filters['status'] === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->where('is_deleted', false)
            ->latest()
            ->paginate($perPage);
    }

    public function createCustomer(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $data = $this->preparePasswordData($data);

            return Customer::create($data);
        });
    }

    public function updateCustomer(Customer $customer, array $data): Customer
    {
        return DB::transaction(function () use ($customer, $data) {
            $data = $this->preparePasswordData($data, isUpdate: true);

            $customer->update($data);

            return $customer->fresh();
        });
    }

    /**
     * Remove empty passwords on update; model casts hash on set.
     */
    private function preparePasswordData(array $data, bool $isUpdate = false): array
    {
        if ($isUpdate && empty($data['password'])) {
            unset($data['password']);
        }

        unset($data['password_confirmation']);

        return $data;
    }

    public function deleteCustomer(Customer $customer): bool
    {
        return DB::transaction(function () use ($customer) {
            return $customer->update(['is_deleted' => true]);
        });
    }
}
