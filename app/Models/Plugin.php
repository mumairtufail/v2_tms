<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasActivityLog;

class Plugin extends Model
{
    use HasFactory, HasActivityLog;

    protected string $activityLabel = 'plugin';
    protected string $activityLabelKey = 'name';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'author',
        'is_active',
        'is_installed',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_installed' => 'boolean',
    ];
}
