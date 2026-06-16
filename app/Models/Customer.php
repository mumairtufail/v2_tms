<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'company_id',
        'name',
        'password',
        'is_active',
        'short_code',
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
        'is_deleted',
        'quickbooks_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'portal' => 'boolean',
        'network_customer' => 'boolean',
        'quote_required' => 'boolean',
        'is_deleted' => 'boolean',
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
}
