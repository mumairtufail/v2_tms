<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'company_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
                    ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
                    ->withPivot(['create', 'update', 'view', 'delete', 'logs', 'others'])
                    ->withTimestamps();
    }

    public function rolePermissions()
    {
        return $this->hasMany(\App\Models\RolePermission::class);
    }

    // Helper methods
    public function hasPermission(string $permissionName, string $action = 'view'): bool
    {
        $permission = $this->permissions()->where('name', $permissionName)->first();
        return $permission && $permission->pivot->$action;
    }

    public function givePermissionTo(int $permissionId, array $actions): void
    {
        $this->permissions()->syncWithoutDetaching([
            $permissionId => $actions
        ]);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
