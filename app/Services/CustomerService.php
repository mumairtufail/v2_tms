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

            if (empty($data['short_code'])) {
                $data['short_code'] = $this->generateUniqueShortCode($data['company_id'], $data['name']);
            }

            return Customer::create($data);
        });
    }

    public function updateCustomer(Customer $customer, array $data): Customer
    {
        return DB::transaction(function () use ($customer, $data) {
            $data = $this->preparePasswordData($data, isUpdate: true);

            // Short code is immutable after creation — it's referenced by
            // downstream records (orders, invoices) and must not silently
            // change on an unrelated profile edit.
            unset($data['short_code']);

            $customer->update($data);

            return $customer->fresh();
        });
    }

    /**
     * Generate a unique 3-char short code for a customer, scoped to the company.
     */
    public function generateUniqueShortCode(int $companyId, string $name, ?int $excludeId = null): string
    {
        $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
        $candidates = [];

        if ($clean !== '') {
            $len = strlen($clean);

            // Primary: first 3 chars (padded with digits if short)
            $base = str_pad(substr($clean, 0, 3), 3, '0');
            $candidates[] = $base;

            // Variations: replace last char, then middle char, with 1-9
            for ($i = 1; $i <= 9; $i++) {
                $candidates[] = substr($base, 0, 2) . $i;
            }
            for ($i = 1; $i <= 9; $i++) {
                $candidates[] = substr($base, 0, 1) . $i . substr($base, 2, 1);
            }

            // Variations: pick chars spread across the name
            for ($offset = 1; $offset < $len - 1; $offset++) {
                $spread = $clean[0] . $clean[min($offset, $len - 1)] . $clean[min($offset * 2, $len - 1)];
                $candidates[] = str_pad(substr($spread, 0, 3), 3, '0');
            }
        }

        foreach ($candidates as $candidate) {
            $candidate = strtoupper(substr($candidate, 0, 3));
            if (!$this->shortCodeExists($candidate, $companyId, $excludeId)) {
                return $candidate;
            }
        }

        // Last resort: random alphanumeric
        do {
            $random = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 3));
        } while ($this->shortCodeExists($random, $companyId, $excludeId));

        return $random;
    }

    private function shortCodeExists(string $code, int $companyId, ?int $excludeId = null): bool
    {
        return Customer::where('company_id', $companyId)
            ->where('short_code', $code)
            ->where('is_deleted', false)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();
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
