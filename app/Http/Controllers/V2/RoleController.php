<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    protected string $logChannel = 'roles';

    public function __construct()
    {
        // Log::channel($this->logChannel); // Ensure channel exists or use default
    }

    public function index(Request $request, $company)
    {
        $query = Role::query()->where('company_id', app('current.company')->id);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $roles = $query->orderBy('created_at', 'desc')
            ->with('permissions') // Eager load permissions
            ->paginate(10)
            ->withQueryString();

        $permissions = Permission::where('is_active', true)->get();

        return view('v2.roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request, $company)
    {
        $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('roles')->where('company_id', app('current.company')->id)
            ],
        ]);

        try {
            Role::create([
                'name' => $request->name,
                'company_id' => app('current.company')->id,
                'is_active' => true
            ]);

            return redirect()->back()->with('success', 'Role created successfully!');

        } catch (\Exception $e) {
            Log::error('Role creation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to create role.');
        }
    }

    public function update(Request $request, $company, Role $role)
    {
        if ($role->company_id !== app('current.company')->id) {
            abort(403);
        }

        $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('roles')->where('company_id', app('current.company')->id)->ignore($role->id)
            ],
        ]);

        try {
            $role->update(['name' => $request->name]);
            return redirect()->back()->with('success', 'Role updated successfully!');

        } catch (\Exception $e) {
            Log::error('Role update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update role.');
        }
    }

    public function updatePermissions(Request $request, $company, Role $role)
    {
        if ($role->company_id !== app('current.company')->id) {
            abort(403);
        }

        $request->validate([
            'permissions' => 'array'
        ]);

        try {
            $syncData = [];
            if ($request->has('permissions')) {
                foreach ($request->permissions as $permId => $data) {
                    $actions = $data['actions'] ?? [];
                    if (!empty($actions)) {
                        $syncData[$permId] = [
                            'create' => in_array('create', $actions),
                            'view' => in_array('view', $actions),
                            'update' => in_array('update', $actions),
                            'delete' => in_array('delete', $actions),
                            'logs' => in_array('logs', $actions),
                            'others' => in_array('others', $actions),
                        ];
                    }
                }
            }
            $role->permissions()->sync($syncData);

            return redirect()->back()->with('success', 'Permissions updated successfully!');

        } catch (\Exception $e) {
            Log::error('Permissions update failed for Role ID ' . $role->id . ': ' . $e->getMessage());
            Log::error('Request Data: ', $request->all());
            return back()->with('error', 'Failed to update permissions: ' . $e->getMessage());
        }
    }

    public function destroy($company, Role $role)
    {
        if ($role->company_id !== app('current.company')->id) {
            abort(403);
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Cannot delete role because it has assigned users.');
        }

        try {
            $role->delete();
            return redirect()->back()->with('success', 'Role deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Role deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete role.');
        }
    }
}
