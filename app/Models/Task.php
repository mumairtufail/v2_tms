<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'status',
        'type',
        'location',
        'assignee',
        'doc',
        'notes',
        'task_start_date',
        'task_start_time',
        'task_end_date',
        'task_end_time',
        'trailer_id',
        'security_id',
        'hours',
        'manifest_id'
    ];
    
    public function manifest()
    {
        return $this->belongsTo(Manifest::class);
    }
}
