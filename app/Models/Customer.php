<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'name',
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
        'quickbooks_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'portal' => 'boolean',
        'network_customer' => 'boolean',
        'quote_required' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    // Relationship with users
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Scope for active customers
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

 public function company()
    {
        return $this->belongsTo(Company::class);
    }


    // Scope for non-deleted customers
    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }
}