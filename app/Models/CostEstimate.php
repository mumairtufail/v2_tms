<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasActivityLog;

class CostEstimate extends Model
{
    use HasFactory, HasActivityLog;

    protected string $activityLabel = 'cost estimate';

    protected $fillable = ['manifest_id','order_id','type','description','qty','rate','est_cost'];

    public function manifest()
    {
        return $this->belongsTo(\App\Models\Manifest::class);
    }

    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class);
    }
}
