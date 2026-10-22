<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
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
            ->withCount('users') // Drives the "assigned users" delete guard in the view
            ->paginate(10)
            ->withQueryString();

        $permissions = Permission::where('is_active', true)->get();

        return view('v2.roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request, $company)
    {
        $request->merge(['name' => trim((string) $request->input('name'))]);

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->where('company_id', app('current.company')->id),
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (Role::isReservedName($value)) {
                        $fail('"'.$value.'" is reserved for the built-in Admin role. Choose another name.');
                    }
                },
            ],
        ], [
            'name.unique' => 'A role with this name already exists.',
        ]);

        if ($validator->fails()) {
            // Reopen the Create dialog with the message, rather than closing it
            // and leaving the person to guess why nothing was added.
            return back()->withErrors($validator, 'roleForm')->withInput()->with('open_role_modal', 'create-role-modal');
        }

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

        $request->merge(['name' => trim((string) $request->input('name'))]);
        $modal = 'edit-role-'.$role->id;

        // The built-in Admin role keeps its name. Renaming it used to switch off
        // every protection (it was recognised by name), and renaming it back was
        // then refused as a reserved name, so a company could not undo it.
        if ($role->isProtectedAdmin()) {
            if (Role::normaliseName($request->input('name')) === Role::normaliseName($role->name)) {
                return back()->with('info', 'No changes to save.');
            }

            Log::warning('Rename of protected Admin role refused', [
                'role_id' => $role->id,
                'company_id' => $role->company_id,
                'attempted_name' => $request->input('name'),
                'user_id' => auth()->id(),
            ]);

            return back()
                ->withErrors(['name' => 'The Admin role is built in and cannot be renamed. To use a different name, create a new role and give it the permissions you need.'], 'roleForm')
                ->withInput()
                ->with('open_role_modal', $modal);
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->where('company_id', app('current.company')->id)->ignore($role->id),
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (Role::isReservedName($value)) {
                        $fail('"'.$value.'" is reserved for the built-in Admin role. Choose another name.');
                    }
                },
            ],
        ], [
            'name.unique' => 'A role with this name already exists.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'roleForm')->withInput()->with('open_role_modal', $modal);
        }

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

        // Admin always carries full access; it is the fallback nothing else
        // in the company can be locked out from, so its grid is not editable.
        if ($role->isProtectedAdmin()) {
            return back()->with('error', 'The Admin role always has full access and its permissions cannot be changed.');
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

        if ($role->isProtectedAdmin()) {
            return back()->with('error', 'The Admin role is created automatically for every company and cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'This role is still assigned to users. Move them to another role first.');
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
