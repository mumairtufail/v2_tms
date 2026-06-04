<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create the settings permission if it doesn't exist
        $permissionId = DB::table('permissions')
            ->where('name', 'settings')
            ->value('id');

        if (! $permissionId) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name'       => 'settings',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Attach to every admin role with full settings access (except delete)
        $adminRoleIds = DB::table('roles')->where('name', 'admin')->pluck('id');

        foreach ($adminRoleIds as $roleId) {
            $exists = DB::table('role_permissions')
                ->where('role_id', $roleId)
                ->where('permission_id', $permissionId)
                ->exists();

            if (! $exists) {
                DB::table('role_permissions')->insert([
                    'role_id'       => $roleId,
                    'permission_id' => $permissionId,
                    'create'        => true,
                    'update'        => true,
                    'view'          => true,
                    'delete'        => false,
                    'logs'          => true,
                    'others'        => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('name', 'settings')->value('id');

        if ($permissionId) {
            DB::table('role_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }
    }
};
