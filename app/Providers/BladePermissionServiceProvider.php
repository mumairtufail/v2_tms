<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Services\PermissionService;

class BladePermissionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // @canPermission('users', 'create')
        Blade::if('canPermission', function ($module, $action = 'view') {
            $permissionService = new PermissionService();
            return $permissionService->can($module, $action);
        });

        // @canAnyPermission('users', ['create', 'update'])
        Blade::if('canAnyPermission', function ($module, $actions) {
            $permissionService = new PermissionService();
            return $permissionService->canAny($module, $actions);
        });

        // @isSuperAdmin
        Blade::if('isSuperAdmin', function () {
            return auth()->check() && auth()->user()->is_super_admin;
        });

        // @isCompanyAdmin
        Blade::if('isCompanyAdmin', function () {
            $permissionService = new PermissionService();
            return $permissionService->isCompanyAdmin();
        });

        // @canAccessCompany($companyId)
        Blade::if('canAccessCompany', function ($companyId) {
            $permissionService = new PermissionService();
            return $permissionService->canAccessCompany($companyId);
        });
    }
}