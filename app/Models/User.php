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
        'two_factor_enabled'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
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
        $this->activityLogs()->create([
            'action' => $action,
            'data' => $description ? json_encode(['description' => $description] + $data) : json_encode($data),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'is_successful' => true,
        ]);
    }

    // Helper methods
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