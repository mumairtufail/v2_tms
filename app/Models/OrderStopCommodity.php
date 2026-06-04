<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStopCommodity extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function stop()
    {
        return $this->belongsTo(OrderStop::class, 'order_stop_id');
    }
}