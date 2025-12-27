<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taskbackup extends Model
{
    use HasFactory;
    protected $fillable = ['status', 'type', 'location', 'assignee', 'doc', 'notes'];
}
