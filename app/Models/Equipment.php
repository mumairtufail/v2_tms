<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasActivityLog;

class Equipment extends Model
{
    use HasFactory, HasActivityLog;

    protected string $activityLabel = 'equipment';
    protected string $activityLabelKey = 'name';
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

