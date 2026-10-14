<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasActivityLog;

class SystemSetting extends Model
{
    use HasActivityLog;

    protected string $activityLabel = 'system setting';
    protected $fillable = ['logo_light', 'logo_dark', 'logo_icon', 'app_name'];

    public static function instance(): static
    {
        return static::firstOrCreate(['id' => 1], ['app_name' => 'TMS']);
    }
}
