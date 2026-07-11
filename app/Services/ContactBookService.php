<?php

namespace App\Services;

use App\Models\ContactBookEntry;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ContactBookService
{
    public static function cacheKey(int $companyId): string
    {
        return "contact_book:{$companyId}";
    }

    /**
     * Full contact book for a tenant, cached until an entry changes
     * (cache is busted by ContactBookEntryObserver).
     */
    public function list(int $companyId): array
    {
        return Cache::rememberForever(self::cacheKey($companyId), function () use ($companyId) {
            return ContactBookEntry::forCompany($companyId)
                ->orderBy('name')
                ->get()
                ->map(fn (ContactBookEntry $e) => [
                    'id' => $e->id,
                    // Frontend field names (order form stop shape uses zip, not postal_code)
                    'company_name' => $e->name,
                    'address_1' => $e->address_1,
                    'address_2' => $e->address_2 ?? '',
                    'city' => $e->city,
                    'state' => $e->state,
                    'zip' => $e->postal_code,
                    'country' => $e->country,
                    'lat' => $e->lat,
                    'lng' => $e->lng,
                    'contact_name' => $e->contact_name,
                    'phone' => $e->phone,
                    'email' => $e->email,
                    '_key' => ContactBookEntry::normalizedKey($e->name, $e->address_1, $e->city, $e->state),
                ])
                ->values()
                ->all();
        });
    }

    /**
     * Idempotently save user-selected addresses into the contact book.
     * Invalid rows are skipped; returns the number of entries saved.
     */
    public function saveEntries(int $companyId, array $entries): int
    {
        $saved = 0;

        foreach (array_slice($entries, 0, 20) as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $name = trim((string) ($entry['name'] ?? $entry['company_name'] ?? ''));
            $address1 = trim((string) ($entry['address_1'] ?? ''));
            $city = trim((string) ($entry['city'] ?? ''));

            if ($name === '' || $address1 === '' || $city === '') {
                continue;
            }

            $state = trim((string) ($entry['state'] ?? '')) ?: null;

            $attributes = [
                'name' => Str::limit($name, 255, ''),
                'address_1' => Str::limit($address1, 255, ''),
                'address_2' => Str::limit(trim((string) ($entry['address_2'] ?? '')), 255, '') ?: null,
                'city' => Str::limit($city, 255, ''),
                'state' => $state ? Str::limit($state, 100, '') : null,
                'postal_code' => Str::limit(trim((string) ($entry['zip'] ?? $entry['postal_code'] ?? '')), 20, '') ?: null,
                'country' => Str::limit(trim((string) ($entry['country'] ?? '')), 10, '') ?: null,
                'lat' => is_numeric($entry['lat'] ?? null) ? (float) $entry['lat'] : null,
                'lng' => is_numeric($entry['lng'] ?? null) ? (float) $entry['lng'] : null,
                'contact_name' => Str::limit(trim((string) ($entry['contact_name'] ?? '')), 255, '') ?: null,
                'phone' => Str::limit(trim((string) ($entry['phone'] ?? '')), 50, '') ?: null,
                'email' => Str::limit(trim((string) ($entry['email'] ?? '')), 255, '') ?: null,
            ];

            $keys = [
                'company_id' => $companyId,
                'dedupe_hash' => ContactBookEntry::dedupeHash($name, $address1, $city, $state),
            ];

            try {
                ContactBookEntry::withoutCompanyScope()->updateOrCreate($keys, $attributes);
            } catch (QueryException $e) {
                // Unique-index race (double submit): the row exists now, update it.
                ContactBookEntry::withoutCompanyScope()->where($keys)->update($attributes);
            }

            $saved++;
        }

        return $saved;
    }
}
