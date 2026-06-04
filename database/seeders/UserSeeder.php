<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
 
    public function run(): void
    {
        $companies = Company::where('is_active', true)->where('is_deleted', false)->get();

        foreach ($companies as $company) {
            $this->createUsersForCompany($company);
        }

        $this->command->info('Users seeded successfully!');
    }

    private function createUsersForCompany(Company $company): void
    {
        // Super Admin (only for first company)
        if ($company->id === 1) {
            $superAdmin = User::firstOrCreate([
                'email' => 'superadmin@system.com',
            ], [
                'f_name' => 'Super',
                'l_name' => 'Admin',
                'password' => Hash::make('password'),
                'is_super_admin' => true,
                'company_id' => $company->id,
                'is_active' => true,
                'is_deleted' => false,
                'email_verified_at' => now(),
            ]);

            // Assign super admin role
            $superAdminRole = Role::where('name', 'super_admin')->where('company_id', $company->id)->first();
            if ($superAdminRole && !$superAdmin->hasRole('super_admin')) {
                $superAdmin->roles()->attach($superAdminRole->id);
            }
        }

        // Company Admin
        $admin = User::firstOrCreate([
            'email' => 'admin@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
        ], [
            'f_name' => 'Company',
            'l_name' => 'Admin',
            'password' => Hash::make('password'),
            'is_super_admin' => false,
            'company_id' => $company->id,
            'is_active' => true,
            'is_deleted' => false,
            'email_verified_at' => now(),
        ]);

        // Assign admin role
        $adminRole = Role::where('name', 'admin')->where('company_id', $company->id)->first();
        if ($adminRole && !$admin->hasRole('admin')) {
            $admin->roles()->attach($adminRole->id);
        }

        // Manager
        $manager = User::firstOrCreate([
            'email' => 'manager@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
        ], [
            'f_name' => 'Company',
            'l_name' => 'Manager',
            'password' => Hash::make('password'),
            'is_super_admin' => false,
            'company_id' => $company->id,
            'is_active' => true,
            'is_deleted' => false,
            'email_verified_at' => now(),
        ]);

        // Assign manager role
        $managerRole = Role::where('name', 'manager')->where('company_id', $company->id)->first();
        if ($managerRole && !$manager->hasRole('manager')) {
            $manager->roles()->attach($managerRole->id);
        }

        // Regular User
        $user = User::firstOrCreate([
            'email' => 'user@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
        ], [
            'f_name' => 'Regular',
            'l_name' => 'User',
            'password' => Hash::make('password'),
            'is_super_admin' => false,
            'company_id' => $company->id,
            'is_active' => true,
            'is_deleted' => false,
            'email_verified_at' => now(),
        ]);

        // Assign user role
        $userRole = Role::where('name', 'user')->where('company_id', $company->id)->first();
        if ($userRole && !$user->hasRole('user')) {
            $user->roles()->attach($userRole->id);
        }

        // Read Only User
        $readOnlyUser = User::firstOrCreate([
            'email' => 'readonly@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
        ], [
            'f_name' => 'Read Only',
            'l_name' => 'User',
            'password' => Hash::make('password'),
            'is_super_admin' => false,
            'company_id' => $company->id,
            'is_active' => true,
            'is_deleted' => false,
            'email_verified_at' => now(),
        ]);

        // Assign read only role
        $readOnlyRole = Role::where('name', 'read_only')->where('company_id', $company->id)->first();
        if ($readOnlyRole && !$readOnlyUser->hasRole('read_only')) {
            $readOnlyUser->roles()->attach($readOnlyRole->id);
        }
    }
}
