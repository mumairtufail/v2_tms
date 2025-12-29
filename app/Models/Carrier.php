<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Carrier extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'carrier_name',
        'dot_id',
        'docket_number',
        'address_1',
        'city',
        'state',
        'post_code',
        'country',
        'currency',
        'is_active'
    ];

    /**
     * Get the company that owns the carrier.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
