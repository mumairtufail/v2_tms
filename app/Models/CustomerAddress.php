<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerAddress extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'company_id',
        'customer_id',
        'company_name',
        'contact_name',
        'phone',
        'phone_ext',
        'fax',
        'email',
        'address_1',
        'address_2',
        'suite',
        'city',
        'state',
        'postal_code',
        'country',
        'lat',
        'lng',
        'open_time',
        'close_time',
        'customs_broker',
        'bol_instructions',
        'shipper_notes',
        'consignee_notes',
        'external_id',
        'is_billing',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'is_billing' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function (CustomerAddress $address) {
            if (!$address->is_billing) {
                return;
            }

            // One billing address per customer.
            static::withoutGlobalScope('company')
                ->where('customer_id', $address->customer_id)
                ->whereKeyNot($address->id)
                ->where('is_billing', true)
                ->update(['is_billing' => false]);

            // Legacy columns are still read by QuickBooks sync and customer search.
            Customer::whereKey($address->customer_id)->update([
                'address' => trim($address->address_1 . ($address->address_2 ? ', ' . $address->address_2 : '')),
                'city' => $address->city,
                'state' => $address->state,
                'postal_code' => $address->postal_code,
                'country' => $address->country,
            ]);
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function accessorials(): BelongsToMany
    {
        return $this->belongsToMany(Accessorial::class, 'customer_address_accessorial')->withoutGlobalScope('company');
    }

    public function openTimeValue(): string
    {
        return $this->open_time ? substr($this->open_time, 0, 5) : '';
    }

    public function closeTimeValue(): string
    {
        return $this->close_time ? substr($this->close_time, 0, 5) : '';
    }

    public function streetLine(): string
    {
        return implode(', ', array_filter([
            $this->address_1,
            $this->suite ? 'Suite ' . $this->suite : null,
            $this->address_2,
        ]));
    }

    public function localityLine(): string
    {
        $stateZip = trim(implode(' ', array_filter([$this->state, $this->postal_code])));

        return implode(', ', array_filter([$this->city, $stateZip, $this->country]));
    }

    /**
     * Same shape as ContactBookService entries, plus the stop defaults this address carries.
     */
    public function toContactBookEntry(): array
    {
        return [
            'id' => 'customer-address-' . $this->id,
            'source' => 'customer',
            'company_name' => $this->company_name,
            'address_1' => $this->address_1,
            'address_2' => implode(', ', array_filter([$this->suite ? 'Suite ' . $this->suite : null, $this->address_2])),
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->postal_code,
            'country' => $this->country,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'contact_name' => $this->contact_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'opening_time' => $this->openTimeValue(),
            'closing_time' => $this->closeTimeValue(),
            'customs_broker' => $this->customs_broker,
            'shipper_notes' => $this->shipper_notes,
            'consignee_notes' => $this->consignee_notes,
            'accessorial_ids' => $this->accessorials->pluck('id')->map(fn ($id) => (string) $id)->values()->all(),
            '_key' => ContactBookEntry::normalizedKey($this->company_name, $this->address_1, $this->city, $this->state),
        ];
    }
}
