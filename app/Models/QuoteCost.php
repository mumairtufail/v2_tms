<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_quote_id',
        'category',
        'type',
        'description',
        'qty',
        'rate',
        'cost',
        'percentage',
    ];

    public function quote()
    {
        return $this->belongsTo(OrderQuote::class, 'order_quote_id');
    }
}
