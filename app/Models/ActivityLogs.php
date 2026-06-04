<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLogs extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'is_successful',
        'data'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
