<?php

// app/Models/User.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'f_name',
        'l_name',
        'email',
        'password',
        'address',
        'phone',
        'is_super_admin',
        'company_id',
        'is_deleted',
        'is_active',
        'profile_image',
        'status',
        'last_login_at',
        'email_notifications',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'remember_token_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'remember_token_expires_at' => 'datetime',
        'password' => 'hashed',
        'is_super_admin' => 'boolean',
        'is_deleted' => 'boolean',
        'is_active' => 'boolean',
        'email_notifications' => 'boolean',
        'two_factor_enabled' => 'boolean',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
                    ->withTimestamps();
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLogs::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function logActivity(string $action, string $description = null, array $data = [])
    {
        app(\App\Services\ActivityLog::class)->log($action, array_merge($data, [
            'description' => $description ?? $action,
            'subject_user_id' => $this->id,
        ]));
    }

    // Helper methods

    /**
     * Single source of truth for the active check. Covers both the legacy
     * is_active boolean and the v2 status string so drifted rows are
     * treated as inactive either way.
     */
    public function isInactive(): bool
    {
        return $this->is_deleted || $this->status === 'inactive' || !$this->is_active;
    }

    public function canAccessCompany(Company $company): bool
    {
        // Super admin can access any company
        if ($this->is_super_admin) {
            return true;
        }

        // Check if user belongs to the company
        return $this->company_id === $company->id;
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function primaryRoleLabel(): string
    {
        if ($this->is_super_admin) {
            return 'Super Admin';
        }

        $role = $this->roles()
            ->when($this->company_id, fn ($query) => $query->where('company_id', $this->company_id))
            ->whereNotIn('name', ['super_admin'])
            ->orderByRaw("CASE name WHEN 'admin' THEN 1 WHEN 'company_admin' THEN 2 ELSE 3 END")
            ->first() ?? $this->roles()->first();

        return match ($role?->name) {
            'admin', 'company_admin' => 'Admin',
            'super_admin' => 'Super Admin',
            default => $role?->name ? \Illuminate\Support\Str::headline($role->name) : 'User',
        };
    }

    public function hasPermission(string $permissionName, string $action = 'view'): bool
    {
        // Super admin has all permissions
        if ($this->is_super_admin) {
            return true;
        }

        // Check through roles
        foreach ($this->roles as $role) {
            $permission = $role->permissions()->where('name', $permissionName)->first();
            if ($permission && $permission->pivot->$action) {
                return true;
            }
        }

        return false;
    }

    public function getAllPermissions(): array
    {
        $permissions = [];
        
        foreach ($this->roles as $role) {
            foreach ($role->permissions as $permission) {
                $key = $permission->name;
                
                if (!isset($permissions[$key])) {
                    $permissions[$key] = [
                        'name' => $permission->name,
                        'create' => false,
                        'update' => false,
                        'view' => false,
                        'delete' => false,
                        'logs' => false,
                        'others' => false,
                    ];
                }
                
                // Grant permission if any role has it
                $permissions[$key]['create'] = $permissions[$key]['create'] || $permission->pivot->create;
                $permissions[$key]['update'] = $permissions[$key]['update'] || $permission->pivot->update;
                $permissions[$key]['view'] = $permissions[$key]['view'] || $permission->pivot->view;
                $permissions[$key]['delete'] = $permissions[$key]['delete'] || $permission->pivot->delete;
                $permissions[$key]['logs'] = $permissions[$key]['logs'] || $permission->pivot->logs;
                $permissions[$key]['others'] = $permissions[$key]['others'] || $permission->pivot->others;
            }
        }
        
        return $permissions;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_deleted', false);
    }

    public function scopeInCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // Accessors
    public function getNameAttribute(): string
    {
        // Support both 'name' field and f_name/l_name combination
        if (isset($this->attributes['name']) && $this->attributes['name']) {
            return $this->attributes['name'];
        }
        return $this->f_name . ' ' . $this->l_name;
    }

    public function getFullNameAttribute(): string
    {
        return $this->f_name . ' ' . $this->l_name;
    }

    public function getProfileImageUrlAttribute(): string
    {
        if ($this->profile_image && Storage::disk('public')->exists('avatars/' . $this->profile_image)) {
            return asset('storage/avatars/' . $this->profile_image);
        }
        
        return '';
    }

    public function getAvatarAttribute(): string
    {
        if ($this->profile_image) {
            return $this->getProfileImageUrlAttribute();
        }
        
        // Return initials as fallback
        $initials = strtoupper(substr($this->f_name ?? '', 0, 1)) . strtoupper(substr($this->l_name ?? '', 0, 1));
        return "data:image/svg+xml;base64," . base64_encode("
            <svg xmlns='http://www.w3.org/2000/svg' width='120' height='120' viewBox='0 0 120 120'>
                <rect width='120' height='120' fill='#007bff'/>
                <text x='60' y='70' font-family='Arial, sans-serif' font-size='48' font-weight='bold' text-anchor='middle' fill='white'>{$initials}</text>
            </svg>
        ");
    }
}