<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasActivityLog;

class Service extends Model
{
    use HasFactory, HasActivityLog;

    protected string $activityLabel = 'service';
    protected string $activityLabelKey = 'name';

    protected $fillable = ['name'];
}
