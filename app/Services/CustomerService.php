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
            return Customer::create($data);
        });
    }

    public function updateCustomer(Customer $customer, array $data): Customer
    {
        return DB::transaction(function () use ($customer, $data) {
            $customer->update($data);
            return $customer->fresh();
        });
    }

    public function deleteCustomer(Customer $customer): bool
    {
        return DB::transaction(function () use ($customer) {
            return $customer->update(['is_deleted' => true]);
        });
    }
}
