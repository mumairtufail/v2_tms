<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Services\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RolesController extends Controller
{

    protected $activityLogService;

    public function __construct(ActivityLog $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    public function view()
    {
        $companyId = Auth::user()->company_id;
        $roles = Role::query()
            ->where('company_id', $companyId)
            ->with(['permissions:id,name'])
            ->withCount([
                'permissions as permissions_count' => function ($q) {
                    $q->where(function ($q) {
                        $q->where('create', 1)
                            ->orWhere('update', 1)
                            ->orWhere('view',   1)
                            ->orWhere('delete', 1)
                            ->orWhere('logs',   1)
                            ->orWhere('others', 1);
                    });
                }
            ])
            ->get();

        return view('dashboard.roles.view', compact('roles'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        // Check if role name already exists for this company
        $existingRole = Role::where('name', $request->name)
            ->where('company_id', auth()->user()->company_id)
            ->first();

        if ($existingRole) {
            return back()->withErrors(['name' => 'Role name already exists for your company.']);
        }

        $role = Role::create([
            'name' => $request->name,
            'company_id' => auth()->user()->company_id,
            'is_active' => true
        ]);

        $action = $request->name . ' role created';
        $additionalData = [
            'is_successful' => true,
            'data' => $role->toArray(),
        ];
        $this->activityLogService->log($action, $additionalData);

        return back()->with('success', 'Role added');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $role = Role::where('id', $id)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();

        // Check if role name already exists for this company (excluding current role)
        $existingRole = Role::where('name', $request->name)
            ->where('company_id', auth()->user()->company_id)
            ->where('id', '!=', $id)
            ->first();

        if ($existingRole) {
            return back()->withErrors(['name' => 'Role name already exists for your company.']);
        }

        $role->update(['name' => $request->name]);

        $action = $role->name . ' role updated';
        $additionalData = [
            'is_successful' => true,
            'data' => $role->toArray(),
        ];
        $this->activityLogService->log($action, $additionalData);

        return back()->with('success', 'Role updated');
    }

    public function destroy($id)
    {
        $role = Role::where('id', $id)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();

        $role->delete();

        $action = $role->name . ' role deleted';
        $this->activityLogService->log($action);

        return back()->with('success', 'Role deleted');
    }

    public function getPermissions($id)
    {
        $role = Role::where('id', $id)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();

        // Get all permissions with their role-specific settings from role_permissions table
        $permissions = Permission::leftJoin('role_permissions', function ($join) use ($id) {
            $join->on('permissions.id', '=', 'role_permissions.permission_id')
                ->where('role_permissions.role_id', '=', $id);
        })
            ->select([
                'permissions.*',
                'role_permissions.create',
                'role_permissions.update',
                'role_permissions.view',
                'role_permissions.delete',
                'role_permissions.logs',
                'role_permissions.others'
            ])
            ->get();

        return response()->json([
            'role' => $role,
            'permissions' => $permissions
        ]);
    }

    public function updatePermissions(Request $request, $id)
    {
        $role = Role::where('id', $id)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();

        $permissions = $request->input('permissions', []);

        // Clear existing permissions
        DB::table('role_permissions')->where('role_id', $id)->delete();

        // Add new permissions
        foreach ($permissions as $permissionId => $actions) {
            DB::table('role_permissions')->insert([
                'role_id' => $id,
                'permission_id' => $permissionId,
                'create' => isset($actions['create']),
                'update' => isset($actions['update']),
                'view' => isset($actions['view']),
                'delete' => isset($actions['delete']),
                'logs' => isset($actions['logs']),
                'others' => isset($actions['others']),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return response()->json(['success' => true]);
    }
}
