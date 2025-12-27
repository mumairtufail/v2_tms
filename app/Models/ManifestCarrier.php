<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManifestCarrier extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'manifest_carriers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'manifest_id',
        'carrier_id'
    ];

    /**
     * Get the manifest that owns the carrier assignment.
     */
    public function manifest()
    {
        return $this->belongsTo(Manifest::class, 'manifest_id');
    }

    /**
     * Get the carrier that is assigned to the manifest.
     */
    public function carrier()
    {
        return $this->belongsTo(Carrier::class, 'carrier_id');
    }
}