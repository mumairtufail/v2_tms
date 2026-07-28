<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'status',
        'order_type',
        'customer_id',
        'company_id',
        'special_instructions',
        'ref_number',
        'customer_po_number',
        'customs_broker',
        'port_of_entry',
        'declared_value',
        'container_number',
        'manifest_id', // Added based on controller usage
        'quickbooks_invoice_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function stops()
    {
        return $this->hasMany(OrderStop::class);
    }

    public function manifest()
    {
        return $this->belongsTo(Manifest::class);
    }

    // Manifests this order is attached to via its stops (order_stops.manifest_id) —
    // the reliable link, since orders.manifest_id is not kept in sync by the order save flow.
    public function manifests()
    {
        return $this->belongsToMany(Manifest::class, 'order_stops', 'order_id', 'manifest_id')->distinct();
    }

    public function quote()
    {
        return $this->hasOne(OrderQuote::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }
}
