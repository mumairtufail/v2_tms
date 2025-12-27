<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrier extends Model
{
    use HasFactory;

    protected $fillable = [
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
}
