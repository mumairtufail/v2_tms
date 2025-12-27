<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Manifest; // Added for relationship

class Stop extends Model
{
    use HasFactory;

    protected $fillable = [
        'location',
        'company',
        'address1',
        'address2',
        'city',
        'state',
        'country',
        'postal',
        'manifest_id',
    ];
    
    // Relationship to Manifest
    public function manifest()
    {
        return $this->belongsTo(Manifest::class);
    }
}
