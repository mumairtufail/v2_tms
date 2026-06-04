<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStop extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_appointment' => 'boolean',
        'consignee_data' => 'json',
        'billing_data' => 'json',
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function commodities()
    {
        return $this->hasMany(OrderStopCommodity::class);
    }

    public function accessorials()
    {
        return $this->belongsToMany(Accessorial::class, 'order_stop_accessorials');
    }

    public function manifest()
    {
        return $this->belongsTo(Manifest::class);
    }
}