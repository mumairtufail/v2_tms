<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Stop; // Added for relationship

class Manifest extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'status',
        'start_date',
        'previous_stop',
        'next_stop',
        'freight',
        'draft',
        'manifest_document'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Relationship to orders
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Many-to-many relationship with drivers through manifest_drivers
    public function drivers()
    {
        return $this->belongsToMany(CompanyUser::class, 'manifest_drivers', 'manifest_id', 'driver_id');
    }

    // Many-to-many relationship with carriers through manifest_carriers  
    public function carriers()
    {
        return $this->belongsToMany(Carrier::class, 'manifest_carriers');
    }

    // Many-to-many relationship with equipment through manifest_equipment
    public function equipments()
    {
        return $this->belongsToMany(Equipment::class, 'manifest_equipment');
    }

    // Relationship to stops
    public function stops()
    {
        return $this->hasMany(Stop::class);
    }
    
    // Add relationships for cost estimates
    public function costEstimates()
    {
        return $this->hasMany(CostEstimate::class);
    }
    
    // Add relationships for manifest equipment
    public function manifestEquipment()
    {
        return $this->hasMany(ManifestEquipment::class);
    }
    
    // Add relationships for manifest drivers
    public function manifestDrivers()
    {
        return $this->hasMany(ManifestDriver::class);
    }
}
