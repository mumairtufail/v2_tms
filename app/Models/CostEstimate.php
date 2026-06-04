<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostEstimate extends Model
{
    use HasFactory;

    protected $fillable = ['manifest_id','type','description','qty','rate','est_cost'];

    public function manifest()
    {
        return $this->belongsTo(\App\Models\Manifest::class);
    }
}
