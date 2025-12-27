<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanyTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'name' => 'Logistics Pro',
                'slug' => 'logistics-pro',
                'admin_email' => 'admin@logisticspro.com',
            ],
            [
                'name' => 'Fast Freight',
                'slug' => 'fast-freight',
                'admin_email' => 'admin@fastfreight.com',
            ],
        ];

        $permissions = Permission::all();

        foreach ($companies as $companyData) {
            $company = Company::firstOrCreate([
                'slug' => $companyData['slug']
            ], [
                'name' => $companyData['name'],
                'address' => $companyData['name'] . ' Headquarters',
                'phone' => '+1-555-000-' . rand(1000, 9999),
                'is_active' => true,
            ]);

            // Create Admin Role for this company
            $adminRole = Role::firstOrCreate([
                'name' => 'company_admin',
                'company_id' => $company->id,
            ], [
                'is_active' => true,
            ]);

            // Assign permissions
            $rolePermissions = [];
            foreach ($permissions as $permission) {
                $rolePermissions[$permission->id] = [
                    'create' => true,
                    'update' => true,
                    'view' => true,
                    'delete' => true,
                    'logs' => true,
                    'others' => true,
                ];
            }
            $adminRole->permissions()->sync($rolePermissions);

            // Create Company Admin User
            $adminUser = User::firstOrCreate([
                'email' => $companyData['admin_email']
            ], [
                'name' => $companyData['name'] . ' Admin',
                'f_name' => $companyData['name'],
                'l_name' => 'Admin',
                'password' => Hash::make('password'),
                'is_super_admin' => false,
                'company_id' => $company->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            if (!$adminUser->roles()->where('role_id', $adminRole->id)->exists()) {
                $adminUser->roles()->attach($adminRole->id);
            }
        }
    }
}
