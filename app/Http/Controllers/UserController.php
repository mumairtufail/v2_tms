<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Company;
use App\Services\ActivityLog;
use App\Services\PermissionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected $activityLogService;
    protected $permissionService;

    public function __construct(ActivityLog $activityLogService, PermissionService $permissionService)
    {
        $this->activityLogService = $activityLogService;
        $this->permissionService = $permissionService;
    }

    public function index()
    {
        // Check permission
        if (!$this->permissionService->can('users', 'view')) {
            return redirect()->back()->with('error', 'You do not have permission to view users.');
        }

        $user = auth()->user();
        
        if ($user->is_super_admin) {
            // Super admin can see all users from all companies
            $users = User::with(['roles', 'company'])
                ->where('is_deleted', 0)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            // Regular users can only see users from their company
            $users = User::with(['roles'])
                ->where('is_deleted', 0)
                ->where('company_id', $user->company_id)
                ->where('is_super_admin', 0) // Don't show super admin to regular users
                ->orderBy('id', 'desc')
                ->get();
        }

        $roles = Role::all();
        $permissions = Permission::all();

        return view('dashboard.users.view', compact('users', 'roles', 'permissions'));
    }

    public function Viewcreate()
    {
        // Check permission
        if (!$this->permissionService->can('users', 'create')) {
            return redirect()->back()->with('error', 'You do not have permission to create users.');
        }

        $user = auth()->user();
        
        if ($user->is_super_admin) {
            // Super admin can assign any role from any company
            $roles = Role::with('company')->where('name', '!=', 'customer')->get();
            $companies = Company::where('is_active', 1)->where('is_deleted', 0)->get();
        } else {
            // Regular users can only assign roles from their company
            $roles = Role::where('name', '!=', 'customer')
                         ->where('company_id', $user->company_id)
                         ->get();
            $companies = collect([$user->company]); // Only their company
        }
        return view('dashboard.users.create', compact('roles', 'companies'));
    }

    public function store(Request $request)
    {
        // Check permission
        if (!$this->permissionService->can('users', 'create')) {
            return back()->with('error', 'You do not have permission to create users.');
        }

        $user = auth()->user();

        $validationRules = [
            'f_name' => 'required|string|max:255',
            'l_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'address' => 'nullable|string',
            'role' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'required|boolean',
        ];
        
        // Add company_id validation based on user type
        if ($user->is_super_admin) {
            $validationRules['company_id'] = 'required|exists:companies,id';
        } else {
            $validationRules['company_id'] = 'nullable';
        }
        
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $errorMessage = implode(', ', $validator->errors()->all());
            \Log::error('User creation validation failed', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->except(['password'])
            ]);
            return back()->with('error', 'Failed to store. Please try again. ' . $errorMessage);
        }
        
        // Debug log the incoming request
        \Log::info('User creation attempt', [
            'role' => $request->role,
            'company_id' => $request->company_id,
            'email' => $request->email,
            'is_super_admin' => $user->is_super_admin,
            'all_request' => $request->all()
        ]);

        try {
            // Determine company_id
            if ($user->is_super_admin) {
                $companyId = $request->company_id;
            } else {
                $companyId = $user->company_id;
            }

            // Validate that the selected role belongs to the target company
            $role = Role::findOrFail($request->role);
            if (!$user->is_super_admin && $role->company_id !== $user->company_id) {
                return back()->with('error', 'You can only assign roles from your company.');
            }

            $userData = [
                'f_name' => $request->f_name,
                'l_name' => $request->l_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'address' => $request->address,
                'phone' => $request->phone,
                'company_id' => $companyId,
                'is_super_admin' => false, // New users are never super admin
                'is_active' => (bool) $request->is_active,
                'is_deleted' => false,
                'email_verified_at' => now(),
            ];

            $newUser = User::create($userData);

            // ✅ ASSIGN THE ROLE - Enhanced debugging and better handling
            if (!$request->role || empty($request->role)) {
                return back()->with('error', 'Role selection is required.');
            }

            $role = Role::find($request->role);
            if (!$role) {
                return back()->with('error', 'Selected role does not exist.');
            }

            // Validate role belongs to the correct company (if not super admin)
            if (!$user->is_super_admin && $role->company_id !== $user->company_id) {
                return back()->with('error', 'You can only assign roles from your company.');
            }

            // Assign the role using multiple approaches for reliability
            try {
                // First, try the direct database approach
                \DB::table('user_roles')->insert([
                    'user_id' => $newUser->id,
                    'role_id' => $request->role,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Verify the role was attached by reloading
                $newUser->load('roles');
                
                if ($newUser->roles->isEmpty() || !$newUser->roles->contains($request->role)) {
                    // Additional debugging info
                    \Log::error('Role assignment failed after direct DB insert', [
                        'user_id' => $newUser->id,
                        'role_id' => $request->role,
                        'roles_count' => $newUser->roles->count(),
                        'user_roles_table' => \DB::table('user_roles')->where('user_id', $newUser->id)->get()->toArray()
                    ]);
                    return back()->with('error', 'Failed to assign role to user. Please contact administrator.');
                }
                
                \Log::info('Role assignment successful', [
                    'user_id' => $newUser->id,
                    'role_id' => $request->role,
                    'roles_assigned' => $newUser->roles->pluck('name')->toArray()
                ]);
                
            } catch (\Exception $roleException) {
                \Log::error('Role assignment exception', [
                    'user_id' => $newUser->id,
                    'role_id' => $request->role,
                    'exception' => $roleException->getMessage()
                ]);
                return back()->with('error', 'Error assigning role: ' . $roleException->getMessage());
            }

            $action = $request->f_name . ' user created';
            $additionalData = [
                'is_successful' => true,
                'data' => $newUser->toArray(), 
            ];
            $this->activityLogService->log($action, $additionalData);

            return redirect()->route('users.index')->with('success', 'User created successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error occurred: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        // Check permission
        if (!$this->permissionService->can('users', 'update')) {
            return redirect()->back()->with('error', 'You do not have permission to edit users.');
        }

        $user = auth()->user();
        $targetUser = User::with(['roles'])->findOrFail($id);

        // Check if user can edit this specific user
        if (!$user->is_super_admin && $targetUser->company_id !== $user->company_id) {
            return redirect()->back()->with('error', 'You can only edit users from your company.');
        }

        if ($user->is_super_admin) {
            $roles = Role::with('company')->where('name', '!=', 'customer')->get();
            $companies = Company::where('is_active', 1)->where('is_deleted', 0)->get();
        } else {
            $roles = Role::where('name', '!=', 'customer')
                         ->where('company_id', $user->company_id)
                         ->get();
            $companies = collect([$user->company]);
        }

        return view('dashboard.users.edit', compact('targetUser', 'roles', 'companies'));
    }

    public function update(Request $request, $id)
    {
        // Check permission
        if (!$this->permissionService->can('users', 'update')) {
            return back()->with('error', 'You do not have permission to update users.');
        }

        $user = auth()->user();
        $targetUser = User::findOrFail($id);

        // Check if user can edit this specific user
        if (!$user->is_super_admin && $targetUser->company_id !== $user->company_id) {
            return back()->with('error', 'You can only edit users from your company.');
        }

        $validator = Validator::make($request->all(), [
            'f_name' => 'required|string|max:255',
            'l_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'address' => 'nullable|string',
            'role' => 'nullable|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'required|boolean',
            'company_id' => $user->is_super_admin ? 'nullable|exists:companies,id' : 'nullable',
        ]);

        if ($validator->fails()) {
            $errorMessage = implode(', ', $validator->errors()->all());
            return back()->with('error', 'Failed to update. Please try again. ' . $errorMessage);
        }

        try {
            $userData = [
                'f_name' => $request->f_name,
                'l_name' => $request->l_name,
                'email' => $request->email,
                'address' => $request->address,
                'phone' => $request->phone,
                'is_active' => (bool) $request->is_active,
            ];

            // Super admin can change company, regular users cannot
            if ($user->is_super_admin && $request->company_id) {
                $userData['company_id'] = $request->company_id;
            }

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $targetUser->update($userData);

            // Update role if provided
            if ($request->role) {
                $role = Role::findOrFail($request->role);
                
                // Validate role assignment
                if (!$user->is_super_admin && $role->company_id !== $user->company_id) {
                    return back()->with('error', 'You can only assign roles from your company.');
                }
                
                // ✅ SYNC THE ROLE PROPERLY
                $targetUser->roles()->sync([$request->role]);
            }

            $action = $request->f_name . ' user updated';
            $additionalData = [
                'is_successful' => true,
                'data' => $targetUser->toArray(), 
            ];
            $this->activityLogService->log($action, $additionalData);
            
            return redirect()->route('users.index')->with('success', 'User updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error occurred: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        // Check permission
        if (!$this->permissionService->can('users', 'delete')) {
            return back()->with('error', 'You do not have permission to delete users.');
        }

        $user = auth()->user();
        $targetUser = User::findOrFail($id);

        // Check if user can delete this specific user
        if (!$user->is_super_admin && $targetUser->company_id !== $user->company_id) {
            return back()->with('error', 'You can only delete users from your company.');
        }

        // Prevent deleting super admin
        if ($targetUser->is_super_admin) {
            return back()->with('error', 'Cannot delete super admin user.');
        }

        $targetUser->update(['is_deleted' => 1]);

        $action = $targetUser->f_name . ' user deleted';
        $this->activityLogService->log($action);

        return back()->with('success', 'User deleted successfully');
    }
}