<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PluginConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'plugin_slug',
        'name',
        'configuration',
        'is_active',
    ];

    protected $casts = [
        'configuration' => 'encrypted:array',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
