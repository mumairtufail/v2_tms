<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasActivityLog;

class Driver extends Model
{
    use HasFactory, HasActivityLog;

    protected string $activityLabel = 'driver';
    protected string $activityLabelKey = 'name';
}
