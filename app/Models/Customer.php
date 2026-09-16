<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    public const LOCATION_SHARING = [
        'Do not share' => 'Do not share',
        'approximate' => 'Share approximate live location & ETA',
        'exact live location' => 'Share exact live location & ETA',
    ];

    public const TYPES = [
        'shipper' => 'Shipper',
        'broker' => 'Broker',
        'carrier' => 'Carrier',
        'other' => 'Other',
    ];

    public const BILLING_OPTIONS = [
        'shipper' => 'Shipper',
        'consignee' => 'Consignee',
        'third_party' => 'Third party',
    ];

    public const CURRENCIES = ['CAD', 'USD'];

    protected $fillable = [
        'company_id',
        'name',
        'password',
        'is_active',
        'short_code',
        'external_id',
        'credit_limit',
        'credit_balance',
        'credit_balance_synced_at',
        'portal',
        'location_sharing',
        'network_customer',
        'address',
        'city',
        'customer_email',
        'state',
        'postal_code',
        'country',
        'currency',
        'customer_type',
        'default_billing_option',
        'quote_required',
        'require_dimensions',
        'logo_path',
        'is_deleted',
        'quickbooks_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'portal' => 'boolean',
        'network_customer' => 'boolean',
        'quote_required' => 'boolean',
        'require_dimensions' => 'boolean',
        'is_deleted' => 'boolean',
        'credit_limit' => 'decimal:2',
        'credit_balance' => 'decimal:2',
        'credit_balance_synced_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLogs::class);
    }

    // Child records are already tied to this customer, so the request's
    // company scope is dropped (keeps notifications from queue/driver contexts working).

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class)->withoutGlobalScope('company');
    }

    public function billingAddress(): HasOne
    {
        return $this->hasOne(CustomerAddress::class)->withoutGlobalScope('company')->where('is_billing', true);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class)->withoutGlobalScope('company');
    }

    public function portalContacts(): HasMany
    {
        return $this->contacts()->where('portal_access', true);
    }

    public function billingSettings(): HasOne
    {
        return $this->hasOne(CustomerBillingSetting::class);
    }

    public function accessorials(): BelongsToMany
    {
        return $this->belongsToMany(Accessorial::class, 'customer_accessorial')->withoutGlobalScope('company');
    }

    public function commodities(): HasMany
    {
        return $this->hasMany(CustomerCommodity::class)->withoutGlobalScope('company');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }

    public function scopePortalEnabled($query)
    {
        return $query->where('portal', true);
    }

    public function portalLoginUrl(): ?string
    {
        if (! $this->company) {
            return null;
        }

        return route('portal.login', ['company' => $this->company->slug]);
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $letters = array_map(fn ($w) => mb_substr($w, 0, 1), array_slice(array_filter($words), 0, 2));

        return mb_strtoupper(implode('', $letters)) ?: '?';
    }

    /** Stable avatar colour per customer name. */
    public function avatarColor(): string
    {
        $palette = ['#8AA630', '#D39B12', '#3F5BA9', '#D9502F', '#1B9BB8', '#5E9E3A', '#7A4FB5', '#C2417A', '#2F8F83', '#9A6B3F'];

        return $palette[crc32(mb_strtolower((string) $this->name)) % count($palette)];
    }

    public function locationSharingLabel(): string
    {
        return self::LOCATION_SHARING[$this->location_sharing] ?? 'Do not share';
    }
}
