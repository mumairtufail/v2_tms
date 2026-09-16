<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerContactPhone extends Model
{
    public const TYPES = [
        'office' => 'Office',
        'mobile' => 'Mobile',
        'home' => 'Home',
        'fax' => 'Fax',
        'other' => 'Other',
    ];

    protected $fillable = [
        'customer_contact_id',
        'type',
        'number',
        'ext',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CustomerContact::class, 'customer_contact_id');
    }

    public function display(): string
    {
        return $this->number . ($this->ext ? ' ext. ' . $this->ext : '');
    }
}
