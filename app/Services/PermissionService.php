<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PermissionService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Check if user has permission for a module action
     */
    public function can(string $module, string $action = 'view'): bool
    {
        if (!$this->user) {
            return false;
        }

        // Super admin has all permissions (even though they have a company)
        if ($this->user->is_super_admin) {
            return true;
        }

        // Regular users check through roles
        return $this->user->hasPermission($module, $action);
    }

    /**
     * Check multiple permissions at once
     */
    public function canAny(string $module, array $actions): bool
    {
        foreach ($actions as $action) {
            if ($this->can($module, $action)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get accessible modules for navigation
     */
    public function getAccessibleModules(): array
    {
        if (!$this->user) {
            return [];
        }

        if ($this->user->is_super_admin) {
            return [
                'users' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
                'roles' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
                'companies' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
                'orders' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
                'manifests' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
                'tasks' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
                'customers' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
                'equipment' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
                'carriers' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
                'activity_logs' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
                'dashboard' => ['create' => false, 'update' => false, 'view' => true, 'delete' => false, 'logs' => false, 'others' => true],
            ];
        }

        $permissions = $this->user->getAllPermissions();
        $accessibleModules = [];
        
        foreach ($permissions as $module => $actions) {
            if ($this->canAny($module, ['view', 'create', 'update', 'delete'])) {
                $accessibleModules[$module] = $actions;
            }
        }
        
        return $accessibleModules;
    }

    /**
     * Check if user can access specific company data
     * Super Admin can access ALL companies even though they have their own company
     */
    public function canAccessCompany(int $companyId): bool
    {
        if (!$this->user) {
            return false;
        }

        // Super admin can access ALL companies (system-wide access)
        if ($this->user->is_super_admin) {
            return true;
        }

        // Regular users can only access their own company
        return $this->user->company_id === $companyId;
    }

    /**
     * Check if user is admin of their company
     */
    public function isCompanyAdmin(): bool
    {
        if (!$this->user) {
            return false;
        }

        // Super admin is not considered "company admin" - they're system admin
        if ($this->user->is_super_admin) {
            return false;
        }

        return $this->user->hasRole('admin');
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->user && $this->user->is_super_admin;
    }

    /**
     * Get all companies (for super admin) or user's company (for regular users)
     */
    public function getAccessibleCompanies()
    {
        if (!$this->user) {
            return collect();
        }

        if ($this->user->is_super_admin) {
            // Super admin can see all companies except the system company
            return \App\Models\Company::where('is_active', true)
                                    ->where('is_deleted', false)
                                    ->where('name', '!=', 'System Administration')
                                    ->get();
        }

        // Regular users only see their own company
        return collect([$this->user->company]);
    }
}