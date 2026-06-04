<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accessorial extends Model
{
    use HasFactory;

    public function orderStops()
    {
        return $this->belongsToMany(OrderStop::class, 'order_stop_accessorials');
    }
}