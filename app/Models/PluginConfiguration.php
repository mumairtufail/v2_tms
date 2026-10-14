<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasActivityLog;

class PluginConfiguration extends Model
{
    use HasFactory, HasActivityLog;

    protected string $activityLabel = 'plugin configuration';

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
