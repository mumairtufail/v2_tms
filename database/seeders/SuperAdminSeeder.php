<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create all permissions
         $permissions = [
            // Core modules
            'users',
            'roles',
             // Added missing roles permission
            // 'companies',
            
            // Business modules
            'orders',
            'manifests',
            'tasks',
            'customers',
            'equipment',
            'carriers',
            
            // System modules
            // 'activity_logs',
            'logs',
            // 'documents',
            // 'reports',
            
            // Additional modules that might be added
            // 'settings',
            // 'dashboard',
            // 'notifications'
        ];


        $permissionModels = [];
        foreach ($permissions as $permission) {
            $permissionModels[] = Permission::firstOrCreate([
                'name' => $permission
            ], [
                'is_active' => true,
            ]);
        }

        // 2. Create System Company for Super Admin
        $systemCompany = Company::firstOrCreate([
            'slug' => 'system-administration'
        ], [
            'name' => 'System Administration',
            'address' => 'System Admin Office',
            'phone' => '+1-000-000-0000',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        // 3. Create Super Admin Role
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super_admin',
            'company_id' => $systemCompany->id,
        ], [
            'is_active' => true,
        ]);

        // 4. Assign ALL permissions to Super Admin Role
        $allPermissions = [];
        foreach ($permissionModels as $permission) {
            $allPermissions[$permission->id] = [
                'create' => true,
                'update' => true,
                'view' => true,
                'delete' => true,
                'logs' => true,
                'others' => true,
            ];
        }
        $superAdminRole->permissions()->sync($allPermissions);

        // 5. Create Super Admin User
        $superAdminUser = User::firstOrCreate([
            'email' => 'superadmin@gmail.com'
        ], [
            'name' => 'Super Admin',
            'f_name' => 'Super',
            'l_name' => 'Admin',
            'password' => Hash::make('password'),
            'is_super_admin' => true,
            'company_id' => $systemCompany->id,
            'is_active' => true,
            'is_deleted' => false,
            'email_verified_at' => now(),
            'address' => 'System Admin Address',
            'phone' => '+1-000-000-0000',
        ]);

        // 6. Assign Super Admin Role to User
        if (!$superAdminUser->roles()->where('role_id', $superAdminRole->id)->exists()) {
            $superAdminUser->roles()->attach($superAdminRole->id);
        }

        $this->command->info('Super Admin created successfully!');
        $this->command->info('Email: superadmin@gmail.com');
        $this->command->info('Password: 123456');
        $this->command->info('Company: ' . $systemCompany->name);
        $this->command->info('✅ Ready to login!');
    }
}