<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ContactBookEntry extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'address_1',
        'address_2',
        'city',
        'state',
        'postal_code',
        'country',
        'lat',
        'lng',
        'contact_name',
        'phone',
        'email',
        'dedupe_hash',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    protected static function booted(): void
    {
        // Keep the dedupe hash consistent even when entries are edited directly.
        static::saving(function (ContactBookEntry $entry) {
            $entry->dedupe_hash = static::dedupeHash(
                $entry->name,
                $entry->address_1,
                $entry->city,
                $entry->state
            );
        });
    }

    /**
     * Normalized identity key for an address. Mirrored in JS
     * (resources/js/contact-book.js normalizedKey) — keep in sync.
     */
    public static function normalizedKey(?string $name, ?string $address1, ?string $city, ?string $state): string
    {
        $norm = fn ($v) => mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $v)));

        return implode('|', [$norm($name), $norm($address1), $norm($city), $norm($state)]);
    }

    public static function dedupeHash(?string $name, ?string $address1, ?string $city, ?string $state): string
    {
        return sha1(static::normalizedKey($name, $address1, $city, $state));
    }
}
