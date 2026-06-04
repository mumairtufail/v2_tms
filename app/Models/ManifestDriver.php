<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManifestDriver extends Model
{
    use HasFactory;

    protected $fillable = [
        'manifest_id',
        'driver_id',
    ];

    /**
     * Get the manifest associated with the driver assignment.
     */
    public function manifest()
    {
        return $this->belongsTo(Manifest::class);
    }

    /**
     * Get the driver (user) associated with the assignment.
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
