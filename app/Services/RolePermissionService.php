<?php
namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class RolePermissionService
{
    public function createRole(string $name, int $companyId, array $permissions = []): Role
    {
        DB::beginTransaction();
        
        try {
            $role = Role::create([
                'name' => $name,
                'company_id' => $companyId,
                'is_active' => true,
            ]);

            if (!empty($permissions)) {
                $role->permissions()->sync($permissions);
            }

            DB::commit();
            return $role;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function assignRoleToUser(User $user, int $roleId): bool
    {
        $role = Role::where('id', $roleId)
                   ->where('company_id', $user->company_id)
                   ->where('is_active', true)
                   ->first();
        
        if (!$role) {
            return false;
        }

        if (!$user->roles()->where('role_id', $roleId)->exists()) {
            $user->roles()->attach($roleId);
        }

        return true;
    }

    public function removeRoleFromUser(User $user, int $roleId): bool
    {
        $user->roles()->detach($roleId);
        return true;
    }

    public function syncUserRoles(User $user, array $roleIds): bool
    {
        // Validate all roles belong to user's company
        $validRoles = Role::whereIn('id', $roleIds)
                         ->where('company_id', $user->company_id)
                         ->where('is_active', true)
                         ->pluck('id')
                         ->toArray();

        $user->roles()->sync($validRoles);
        return true;
    }

    public function assignPermissionsToRole(Role $role, array $permissions): void
    {
        $role->permissions()->sync($permissions);
    }

    public function createDefaultRolesForCompany(Company $company): void
    {
        $permissions = Permission::where('is_active', true)->get();
        
        // Admin Role
        $adminRole = $this->createRole('admin', $company->id);
        $adminPermissions = [];
        foreach ($permissions as $permission) {
            $adminPermissions[$permission->id] = [
                'create' => true,
                'update' => true,
                'view' => true,
                'delete' => true,
                'logs' => true,
                'others' => true,
            ];
        }
        $this->assignPermissionsToRole($adminRole, $adminPermissions);

        // Manager Role
        $managerRole = $this->createRole('manager', $company->id);
        $managerPermissions = [];
        foreach ($permissions as $permission) {
            $managerPermissions[$permission->id] = [
                'create' => true,
                'update' => true,
                'view' => true,
                'delete' => false,
                'logs' => true,
                'others' => false,
            ];
        }
        $this->assignPermissionsToRole($managerRole, $managerPermissions);

        // User Role
        $userRole = $this->createRole('user', $company->id);
        $userPermissions = [];
        foreach ($permissions as $permission) {
            $userPermissions[$permission->id] = [
                'create' => false,
                'update' => false,
                'view' => true,
                'delete' => false,
                'logs' => false,
                'others' => false,
            ];
        }
        $this->assignPermissionsToRole($userRole, $userPermissions);
    }
}