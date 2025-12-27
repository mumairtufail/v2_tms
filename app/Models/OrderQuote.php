<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderQuote extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'service_id',
        'notes',
        'delivery_start_date',
        'delivery_end_date',
    ];

    protected $casts = [
        'delivery_start_date' => 'date',
        'delivery_end_date' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function costs()
    {
        return $this->hasMany(QuoteCost::class);
    }
}
