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
        'is_active',
        'is_system',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Names nobody may give a role of their own, because the app treats a role
     * with that name as an admin (User::isCompanyAdmin, primaryRoleLabel, the
     * super admin user list). Compared after normalising case, spaces, dashes
     * and underscores, so "Company Admin" and " ADMIN " are caught as well.
     */
    public const RESERVED_NAMES = ['admin', 'company_admin', 'super_admin'];

    public static function normaliseName(?string $name): string
    {
        return trim((string) preg_replace('/[\s_-]+/', '_', strtolower(trim((string) $name))), '_');
    }

    public static function isReservedName(?string $name): bool
    {
        return in_array(static::normaliseName($name), static::RESERVED_NAMES, true);
    }

    /**
     * Last line of defence for the built-in Admin role, below the controller
     * checks: whatever path tries it (a form, tinker, a future API), the role
     * cannot be renamed or deleted. Blocked attempts are logged.
     */
    protected static function booted(): void
    {
        static::updating(function (Role $role) {
            if (! $role->isDirty('name')) {
                return null;
            }

            $original = (clone $role)->setRawAttributes($role->getOriginal());

            if ($original->isProtectedAdmin()) {
                \Illuminate\Support\Facades\Log::warning('Blocked rename of protected Admin role', [
                    'role_id' => $role->id,
                    'company_id' => $role->company_id,
                    'from' => $role->getOriginal('name'),
                    'to' => $role->name,
                    'user_id' => auth()->id(),
                ]);

                return false;
            }

            return null;
        });

        static::deleting(function (Role $role) {
            if ($role->isProtectedAdmin()) {
                \Illuminate\Support\Facades\Log::warning('Blocked delete of protected Admin role', [
                    'role_id' => $role->id,
                    'company_id' => $role->company_id,
                    'user_id' => auth()->id(),
                ]);

                return false;
            }

            return null;
        });
    }

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
     * to always carry full access. It cannot be renamed or deleted, and its
     * permission grid cannot be edited away from full access - all guarded here
     * so every caller (controller, console, future API) gets the same answer.
     *
     * Recognised by the is_system flag first. The name check stays as a
     * fallback for rows created before that column existed; it must not be the
     * only check, because renaming the role used to switch its protection off.
     */
    public function isProtectedAdmin(): bool
    {
        if ($this->is_system) {
            return true;
        }

        return in_array(static::normaliseName($this->name), ['admin', 'company_admin'], true);
    }

    /** "admin" / "company_admin" read as "Admin" on screen. */
    public function getDisplayNameAttribute(): string
    {
        return match (static::normaliseName($this->name)) {
            'admin', 'company_admin' => 'Admin',
            'super_admin' => 'Super Admin',
            default => (string) $this->name,
        };
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
