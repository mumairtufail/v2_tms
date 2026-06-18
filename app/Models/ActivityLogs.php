<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLogs extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'company_id',
        'action',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'is_successful',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'is_successful' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    protected function description(): Attribute
    {
        return Attribute::get(fn () => $this->data['description'] ?? $this->action);
    }

    protected function actorName(): Attribute
    {
        return Attribute::get(function () {
            if ($this->user) {
                return $this->user->name;
            }

            if ($this->customer) {
                return $this->customer->name.' (Customer)';
            }

            return $this->data['actor_name'] ?? 'System';
        });
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query;
    }
}
