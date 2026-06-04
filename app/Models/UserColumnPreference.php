<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserColumnPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'selected_columns', 'page_type'
    ];

    protected $casts = [
        'selected_columns' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}