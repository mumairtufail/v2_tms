<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Traits\HasActivityLog;

class Role extends Model
{
    use HasActivityLog;

    protected string $activityLabel = 'role';
    protected string $activityLabelKey = 'name';

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

    /**
     * The "admin" role is created automatically for every company
     * (RolePermissionService::createDefaultRolesForCompany) and is relied on
     * to always carry full access. It cannot be deleted, and its permission
     * grid cannot be edited away from full access - both guarded here so
     * every caller (controller, console, future API) gets the same answer.
     */
    public function isProtectedAdmin(): bool
    {
        return strtolower($this->name) === 'admin';
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
