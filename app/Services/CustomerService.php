<?php

namespace App\Services;

use App\Models\Accessorial;
use App\Models\Customer;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CustomerService
{
    public const SHORT_CODE_LENGTH = 4;

    private const SORTABLE = ['name', 'short_code', 'is_active', 'location_sharing', 'network_customer', 'created_at'];

    private const ADDRESS_FIELDS = ['address_1', 'address_2', 'city', 'state', 'postal_code', 'country', 'lat', 'lng'];

    private const DETAIL_FIELDS = [
        'name', 'external_id', 'credit_limit', 'is_active', 'require_dimensions', 'network_customer',
        'location_sharing', 'currency', 'customer_type', 'quote_required', 'default_billing_option',
    ];

    public function getCustomers(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'] ?? null, self::SORTABLE, true) ? $filters['sort'] : 'name';
        $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return Customer::query()
            ->when($filters['company_id'] ?? null, fn ($q, $id) => $q->where('company_id', $id))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('short_code', 'like', "%{$search}%")
                      ->orWhere('customer_email', 'like', "%{$search}%");
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
            ->withExists(['portalContacts as has_portal_access'])
            ->withCount('orders')
            ->orderBy($sort, $direction)
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Creates the customer with its billing address and access to every active accessorial.
     */
    public function createCustomer(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::create([
                'company_id' => $data['company_id'],
                'name' => $data['name'],
                'short_code' => ($data['short_code'] ?? null) ?: $this->generateUniqueShortCode($data['company_id'], $data['name']),
                'currency' => $data['currency'],
                'customer_type' => $data['customer_type'] ?? 'other',
                'quote_required' => (bool) ($data['quote_required'] ?? false),
                'default_billing_option' => $data['default_billing_option'] ?? 'shipper',
                'is_active' => true,
            ]);

            $customer->addresses()->create(array_merge(Arr::only($data, self::ADDRESS_FIELDS), [
                'company_id' => $customer->company_id,
                'company_name' => $customer->name,
                'is_billing' => true,
            ]));

            $this->attachAllAccessorials($customer);

            return $customer;
        });
    }

    /**
     * Details tab. The short code is locked after creation: orders and invoices reference it.
     */
    public function updateDetails(Customer $customer, array $data, ?UploadedFile $logo = null, bool $removeLogo = false): Customer
    {
        return DB::transaction(function () use ($customer, $data, $logo, $removeLogo) {
            $attributes = Arr::only($data, self::DETAIL_FIELDS);

            if (($removeLogo || $logo) && $customer->logo_path) {
                Storage::disk('public')->delete($customer->logo_path);
                $attributes['logo_path'] = null;
            }

            if ($logo) {
                $attributes['logo_path'] = $logo->store("customers/{$customer->company_id}/logos", 'public');
            }

            $customer->update($attributes);

            return $customer->fresh();
        });
    }

    public function attachAllAccessorials(Customer $customer): void
    {
        $ids = Accessorial::forCompany($customer->company_id)->active()->pluck('id');

        $customer->accessorials()->syncWithoutDetaching($ids);
    }

    public function canDelete(Customer $customer): bool
    {
        return ! $customer->orders()->exists();
    }

    /**
     * Suggest a unique 4-character short code, scoped to the company.
     * Starts from the name's initials (7 Mountain Logistics → 7ML1), like Rose Rocket codes.
     */
    public function generateUniqueShortCode(int $companyId, string $name, ?int $excludeId = null): string
    {
        $length = self::SHORT_CODE_LENGTH;
        $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
        $words = array_values(array_filter(preg_split('/[^A-Za-z0-9]+/', strtoupper($name)) ?: []));
        $candidates = [];

        if ($clean !== '') {
            $initials = implode('', array_map(fn ($word) => $word[0], $words));
            $base = str_pad(substr($initials . substr($clean, 1), 0, $length - 1), $length - 1, '0');

            for ($i = 1; $i <= 9; $i++) {
                $candidates[] = $base . $i;
            }

            foreach (range('A', 'Z') as $letter) {
                $candidates[] = $base . $letter;
            }

            $candidates[] = str_pad(substr($clean, 0, $length), $length, '0');
        }

        foreach ($candidates as $candidate) {
            if (!$this->shortCodeExists($candidate, $companyId, $excludeId)) {
                return $candidate;
            }
        }

        // Last resort: random alphanumeric
        do {
            $random = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, $length));
        } while ($this->shortCodeExists($random, $companyId, $excludeId));

        return $random;
    }

    private function shortCodeExists(string $code, int $companyId, ?int $excludeId = null): bool
    {
        return Customer::where('company_id', $companyId)
            ->where('short_code', $code)
            ->where('is_deleted', false)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }

    public function deleteCustomer(Customer $customer): bool
    {
        return DB::transaction(function () use ($customer) {
            return $customer->update(['is_deleted' => true]);
        });
    }
}
