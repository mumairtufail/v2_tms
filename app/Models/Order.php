<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasActivityLog;

class Order extends Model
{
    use HasFactory, HasActivityLog;

    protected string $activityLabel = 'order';
    protected string $activityLabelKey = 'order_number';

    protected $fillable = [
        'order_number',
        'status',
        'order_type',
        'created_via',
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

    // Display labels for order_type. The stored keys stay as-is; only the UI wording changes.
    public const TYPE_LABELS = [
        'point_to_point'   => 'Origin-to-Destination',
        'single_shipper'   => 'Multi-Destination',
        'single_consignee' => 'Milk Run',
        'sequence'         => 'Shuttle Loop',
    ];

    public static function typeLabel(?string $type): string
    {
        return self::TYPE_LABELS[$type] ?? ucfirst(str_replace('_', ' ', (string) $type));
    }

    /**
     * Whether the customer started this order from their own portal, as
     * opposed to a staff member entering it on their behalf. A portal order
     * is always origin-to-destination, so staff editing it afterwards must
     * not be able to switch that away (OrderUpdateService enforces this
     * server-side too - the UI lock alone is not the real guard).
     */
    public function isPortalCreated(): bool
    {
        return $this->created_via === 'portal';
    }

    public function getOrderTypeLabelAttribute(): string
    {
        return self::typeLabel($this->order_type);
    }

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
        // Pickup/delivery order matters here - unordered, this relation and
        // Manifest::orderStops() (the same rows, queried by manifest_id instead
        // of order_id) were not guaranteed to come back in the same sequence,
        // so a manifest could show an order's stops in the opposite order from
        // the one it was quoted in.
        return $this->hasMany(OrderStop::class)->orderBy('sequence_number');
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
