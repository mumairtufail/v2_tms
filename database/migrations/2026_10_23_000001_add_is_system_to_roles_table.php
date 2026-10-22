<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Marks each company's built-in Admin role with a flag instead of relying on
 * its name.
 *
 * The Admin role was recognised only by name = 'admin'. Renaming it (the
 * edit form allowed that) removed every protection at once: it could then be
 * deleted or have its permissions stripped, and User::isCompanyAdmin(), which
 * checks hasRole('admin'), stopped treating its members as admins. Renaming it
 * back was refused as a reserved name, so the company was stuck.
 *
 * This migration:
 *   1. adds roles.is_system
 *   2. flags every role named admin / company_admin (any case)
 *   3. for a company left with no flagged role, finds a single role that
 *      looks like a renamed Admin ("Adminss", "Admin 2", "administrator"),
 *      restores its name to "admin" and flags it
 *   4. tops the flagged roles up to full access on every active permission,
 *      since their grid is locked and could not pick up modules added later
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('roles', 'is_system')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->boolean('is_system')->default(false)->after('name');
            });
        }

        DB::table('roles')
            ->whereRaw('LOWER(TRIM(name)) IN (?, ?)', ['admin', 'company_admin'])
            ->update(['is_system' => true]);

        $this->repairRenamedAdminRoles();
        $this->grantFullAccessToSystemRoles();
    }

    public function down(): void
    {
        if (Schema::hasColumn('roles', 'is_system')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('is_system');
            });
        }
    }

    private function repairRenamedAdminRoles(): void
    {
        $companiesWithSystemRole = DB::table('roles')->where('is_system', true)->pluck('company_id')->unique();

        $orphanCompanies = DB::table('roles')
            ->whereNotIn('company_id', $companiesWithSystemRole)
            ->distinct()
            ->pluck('company_id');

        foreach ($orphanCompanies as $companyId) {
            $candidates = DB::table('roles')
                ->where('company_id', $companyId)
                ->get(['id', 'name'])
                ->filter(function ($role) {
                    $letters = preg_replace('/[^a-z]/', '', strtolower((string) $role->name));

                    return str_starts_with($letters, 'admin') && $letters !== 'superadmin';
                });

            // Only act when the answer is unambiguous. Two candidates means a
            // person has to decide, so it is logged and left alone.
            if ($candidates->count() !== 1) {
                if ($candidates->count() > 1) {
                    Log::warning('Admin role repair skipped: more than one candidate', [
                        'company_id' => $companyId,
                        'roles' => $candidates->pluck('name', 'id')->all(),
                    ]);
                }

                continue;
            }

            $role = $candidates->first();

            DB::table('roles')->where('id', $role->id)->update([
                'name' => 'admin',
                'is_system' => true,
                'updated_at' => now(),
            ]);

            Log::info('Restored renamed Admin role', [
                'company_id' => $companyId,
                'role_id' => $role->id,
                'previous_name' => $role->name,
            ]);
        }
    }

    private function grantFullAccessToSystemRoles(): void
    {
        $permissionIds = DB::table('permissions')->where('is_active', true)->pluck('id');

        if ($permissionIds->isEmpty()) {
            return;
        }

        $full = ['create' => true, 'update' => true, 'view' => true, 'delete' => true, 'logs' => true, 'others' => true];

        foreach (DB::table('roles')->where('is_system', true)->pluck('id') as $roleId) {
            foreach ($permissionIds as $permissionId) {
                $exists = DB::table('role_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if ($exists) {
                    DB::table('role_permissions')
                        ->where('role_id', $roleId)
                        ->where('permission_id', $permissionId)
                        ->update($full + ['updated_at' => now()]);
                } else {
                    DB::table('role_permissions')->insert($full + [
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
};
