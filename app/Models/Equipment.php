<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'name',
        'company_id',
        'desc',
        'sub_type',
        'status',
        'manifest_id',
        'last_seen',
        'last_location'
    ];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function manifest()
    {
        return $this->belongsTo(Manifest::class);
    }
    
}

