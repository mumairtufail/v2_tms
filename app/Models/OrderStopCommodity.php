<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasActivityLog;

class OrderStopCommodity extends Model
{
    use HasFactory, HasActivityLog;

    protected string $activityLabel = 'commodity';
    protected $guarded = [];

    public function stop()
    {
        return $this->belongsTo(OrderStop::class, 'order_stop_id');
    }
}