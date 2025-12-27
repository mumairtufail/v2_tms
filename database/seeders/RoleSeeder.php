<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
    {
        $permissions = Permission::where('is_active', true)->get();
        $companies = Company::where('is_active', true)->where('is_deleted', false)->get();

        foreach ($companies as $company) {
            $this->createRolesForCompany($company, $permissions);
        }

        $this->command->info('Roles seeded successfully!');
    }

    private function createRolesForCompany(Company $company, $permissions): void
    {
        // Super Admin Role (only for first company)
        if ($company->id === 1) {
            $superAdminRole = Role::firstOrCreate([
                'name' => 'super_admin',
                'company_id' => $company->id,
            ], [
                'is_active' => true,
            ]);

            $this->assignAllPermissions($superAdminRole, $permissions);
        }

        // Admin Role - Full access except super admin functions
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'company_id' => $company->id,
        ], [
            'is_active' => true,
        ]);

        $this->assignAdminPermissions($adminRole, $permissions);

        // Manager Role - Business operations management
        $managerRole = Role::firstOrCreate([
            'name' => 'manager',
            'company_id' => $company->id,
        ], [
            'is_active' => true,
        ]);

        $this->assignManagerPermissions($managerRole, $permissions);

        // User Role - Basic operations
        $userRole = Role::firstOrCreate([
            'name' => 'user',
            'company_id' => $company->id,
        ], [
            'is_active' => true,
        ]);

        $this->assignUserPermissions($userRole, $permissions);

        // Read Only Role - View only access
        $readOnlyRole = Role::firstOrCreate([
            'name' => 'read_only',
            'company_id' => $company->id,
        ], [
            'is_active' => true,
        ]);

        $this->assignReadOnlyPermissions($readOnlyRole, $permissions);
    }

    private function assignAllPermissions(Role $role, $permissions): void
    {
        $permissionData = [];
        foreach ($permissions as $permission) {
            $permissionData[$permission->id] = [
                'create' => true,
                'update' => true,
                'view' => true,
                'delete' => true,
                'logs' => true,
                'others' => true,
            ];
        }
        $role->permissions()->sync($permissionData);
    }

    private function assignAdminPermissions(Role $role, $permissions): void
    {
        $adminPermissions = [
            'users' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
            'roles' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
            'orders' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
            'manifests' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
            'tasks' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
            'customers' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
            'equipment' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
            'carriers' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
            'activity_logs' => ['create' => false, 'update' => false, 'view' => true, 'delete' => false, 'logs' => true, 'others' => true],
            'logs' => ['create' => false, 'update' => false, 'view' => true, 'delete' => false, 'logs' => true, 'others' => true],
            'documents' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
            'reports' => ['create' => true, 'update' => true, 'view' => true, 'delete' => false, 'logs' => true, 'others' => true],
            'settings' => ['create' => true, 'update' => true, 'view' => true, 'delete' => false, 'logs' => true, 'others' => true],
            'dashboard' => ['create' => false, 'update' => false, 'view' => true, 'delete' => false, 'logs' => false, 'others' => true],
        ];

        $this->assignSpecificPermissions($role, $permissions, $adminPermissions);
    }

    private function assignManagerPermissions(Role $role, $permissions): void
    {
        $managerPermissions = [
            'users' => ['create' => false, 'update' => true, 'view' => true, 'delete' => false, 'logs' => true, 'others' => false],
            'orders' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
            'manifests' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
            'tasks' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true],
            'customers' => ['create' => true, 'update' => true, 'view' => true, 'delete' => false, 'logs' => true, 'others' => true],
            'equipment' => ['create' => true, 'update' => true, 'view' => true, 'delete' => false, 'logs' => true, 'others' => true],
            'carriers' => ['create' => true, 'update' => true, 'view' => true, 'delete' => false, 'logs' => true, 'others' => true],
            'activity_logs' => ['create' => false, 'update' => false, 'view' => true, 'delete' => false, 'logs' => false, 'others' => false],
            'logs' => ['create' => false, 'update' => false, 'view' => true, 'delete' => false, 'logs' => false, 'others' => false],
            'documents' => ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => false, 'others' => true],
            'reports' => ['create' => true, 'update' => false, 'view' => true, 'delete' => false, 'logs' => false, 'others' => true],
            'dashboard' => ['create' => false, 'update' => false, 'view' => true, 'delete' => false, 'logs' => false, 'others' => false],
        ];

        $this->assignSpecificPermissions($role, $permissions, $managerPermissions);
    }

    private function assignUserPermissions(Role $role, $permissions): void
    {
        $userPermissions = [
            'orders' => ['create' => true, 'update' => true, 'view' => true, 'delete' => false, 'logs' => false, 'others' => false],
            'manifests' => ['create' => true, 'update' => true, 'view' => true, 'delete' => false, 'logs' => false, 'others' => false],
            'tasks' => ['create' => true, 'update' => true, 'view' => true, 'delete' => false, 'logs' => false, 'others' => false],
            'customers' => ['create' => false, 'update' => false, 'view' => true, 'delete' => false, 'logs' => false, 'others' => false],
            'equipment' => ['create' => false, 'update' => false, 'view' => true, 'delete' => false, 'logs' => false, 'others' => false],
            'carriers' => ['create' => false, 'update' => false, 'view' => true, 'delete' => false, 'logs' => false, 'others' => false],
            'documents' => ['create' => true, 'update' => false, 'view' => true, 'delete' => false, 'logs' => false, 'others' => false],
            'dashboard' => ['create' => false, 'update' => false, 'view' => true, 'delete' => false, 'logs' => false, 'others' => false],
        ];

        $this->assignSpecificPermissions($role, $permissions, $userPermissions);
    }

    private function assignReadOnlyPermissions(Role $role, $permissions): void
    {
        $readOnlyPermissions = [
            'orders' => ['create' => false, 'update' => false, 'view' => true, 'delete' => false, 'logs' => false, 'others' => false],
            'manifests' => ['create' => false, 'update' => false, 'view' => true, 'delete' => false, 'logs' => false, 'others' => false],
            'tasks' => ['create' => false, 'update' => false, 'view' => true, 'delete' => false, 'logs' => false, 'others' => false],
            'customers' => ['create' => false, 'update' => false, 'view' => true, 'delete' => false, 'logs' => false, 'others' => false],
            'equipment' => ['create' => false, 'update' => false, 'view' => true, 'delete' => false, 'logs' => false, 'others' => false],
            'carriers' => ['create' => false, 'update' => false, 'view' => true, 'delete' => false, 'logs' => false, 'others' => false],
            'documents' => ['create' => false, 'update' => false, 'view' => true, 'delete' => false, 'logs' => false, 'others' => false],
            'dashboard' => ['create' => false, 'update' => false, 'view' => true, 'delete' => false, 'logs' => false, 'others' => false],
        ];

        $this->assignSpecificPermissions($role, $permissions, $readOnlyPermissions);
    }

    private function assignSpecificPermissions(Role $role, $permissions, array $specificPermissions): void
    {
        $permissionData = [];
        foreach ($permissions as $permission) {
            if (isset($specificPermissions[$permission->name])) {
                $permissionData[$permission->id] = $specificPermissions[$permission->name];
            }
        }
        $role->permissions()->sync($permissionData);
    }
}

