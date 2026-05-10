<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['logo_light', 'logo_dark', 'logo_icon', 'app_name'];

    public static function instance(): static
    {
        return static::firstOrCreate(['id' => 1], ['app_name' => 'TMS']);
    }
}
