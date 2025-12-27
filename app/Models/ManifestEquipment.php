<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManifestEquipment extends Model
{
    use HasFactory;
    
    protected $table = 'manifest_equipment';
    
    protected $fillable = [
        'manifest_id',
        'equipment_id',
    ];
    
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
    
    public function manifest()
    {
        return $this->belongsTo(Manifest::class);
    }
}
