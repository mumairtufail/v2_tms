<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasActivityLog;

class QuoteCost extends Model
{
    use HasFactory, HasActivityLog;

    protected string $activityLabel = 'quote cost';

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
