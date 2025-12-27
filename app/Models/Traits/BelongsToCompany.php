<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Company;

trait BelongsToCompany
{
    /**
     * Boot the trait.
     */
    protected static function bootBelongsToCompany(): void
    {
        // Auto-scope queries to current company
        static::addGlobalScope('company', function (Builder $builder) {
            if ($companyId = config('app.current_company_id')) {
                $builder->where('company_id', $companyId);
            }
        });

        // Auto-set company_id on creation
        static::creating(function ($model) {
            if (!$model->company_id && $companyId = config('app.current_company_id')) {
                $model->company_id = $companyId;
            }
        });
    }

    /**
     * Get the company that owns the model.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope query to specific company.
     */
    public function scopeForCompany(Builder $query, $companyId): Builder
    {
        return $query->withoutGlobalScope('company')->where('company_id', $companyId);
    }

    /**
     * Get query without company scope.
     */
    public function scopeWithoutCompanyScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('company');
    }
}
