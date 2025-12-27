<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
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

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission
            ], [
                'is_active' => true,
            ]);
        }

        $this->command->info('Permissions seeded successfully!');
    }
}