<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Saved commodity preset. Values match the order form's commodity row.
 */
class CustomerCommodity extends Model
{
    use BelongsToCompany;

    public const TYPES = [
        'skid' => 'Skid',
        'container' => 'Container',
        'pallet' => 'Pallet',
        'crate' => 'Crate',
        'drum' => 'Drum',
        'box' => 'Box',
        'bag' => 'Bag',
        'bundle' => 'Bundle',
        'roll' => 'Roll',
        'loose' => 'Loose',
    ];

    public const UNITS = [
        'in_lbs' => 'in/lbs',
        'cm_kg' => 'cm/kg',
    ];

    public const FREIGHT_CLASSES = ['50', '55', '60', '65', '70', '77.5', '85', '92.5', '100', '110', '125', '150', '175', '200', '250', '300', '400', '500'];

    /** CSV columns, in order, for export, import and the template. */
    public const CSV_COLUMNS = ['description', 'type', 'unit', 'volume', 'weight', 'linear_feet', 'length', 'width', 'height', 'freight_class', 'nmfc', 'sku'];

    protected $fillable = [
        'company_id',
        'customer_id',
        'description',
        'type',
        'measurement_unit',
        'volume',
        'weight',
        'linear_feet',
        'length',
        'width',
        'height',
        'freight_class',
        'nmfc',
        'sku',
    ];

    protected $casts = [
        'volume' => 'float',
        'weight' => 'float',
        'linear_feet' => 'float',
        'length' => 'float',
        'width' => 'float',
        'height' => 'float',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function weightUnit(): string
    {
        return $this->measurement_unit === 'cm_kg' ? 'kg' : 'lbs';
    }

    /** Values the order form copies into a commodity row. sku/nmfc are searchable, not copied. */
    public function toOrderPreset(): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'type' => $this->type,
            'weight' => $this->weight,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'lf' => $this->linear_feet,
            'cube' => $this->volume,
            'freight_class' => $this->freight_class,
            'unit' => $this->measurement_unit,
            'sku' => $this->sku,
            'nmfc' => $this->nmfc,
        ];
    }
}
