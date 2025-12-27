<?php

namespace App\Http\Controllers;

use App\Models\CompanyUser;
use App\Models\Role;
use App\Services\ActivityLog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Company;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;

class CompanyUserController extends Controller
{
    protected $activityLogService;

    public function __construct(ActivityLog $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }
    public function index(Request $request)
    {
        $user = auth()->user();
        
        if ($user->is_super_admin) {
            // Super admin can see all users from all companies
            $query = User::with(['roles', 'company'])
                ->where('is_deleted', 0);
        } else {
            // Regular users can only see users from their company
            $query = User::with(['roles', 'company'])
                ->where('is_deleted', 0)
                ->where('company_id', $user->company_id)
                ->where('is_super_admin', 0); // Don't show super admin to regular users
        }

        // Apply search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('f_name', 'LIKE', "%{$search}%")
                  ->orWhere('l_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhereRaw("CONCAT(f_name, ' ', l_name) LIKE ?", ["%{$search}%"]);
            });
        }

        // Filter by company (only for super admin)
        if ($request->filled('company_id') && $user->is_super_admin) {
            $query->where('company_id', $request->company_id);
        }

        // Filter by role
        if ($request->filled('role_id')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('roles.id', $request->role_id);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', 1);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', 0);
            }
        }

        $users = $query->orderBy('id', 'desc')->paginate(20);
        
        // Get all companies for filter (only for super admin)
        $companies = $user->is_super_admin ? Company::orderBy('name')->get() : collect();
        
        $roles = Role::all();
        $permissions = Permission::all();

        return view('dashboard.users.view', compact('users', 'roles', 'permissions', 'companies'));
    }

    public function Viewcreate()
    {


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
        $validator = Validator::make($request->all(), [
            'f_name' => 'required|string|max:255',
            'l_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'address' => 'nullable|string',
            'customer_id' => 'nullable|exists:customers,id',
            'role' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        if ($validator->fails()) {
            $errorMessage = implode(', ', $validator->errors()->all());
            return back()->with('error', 'Failed to store .Please try again. ' . $errorMessage);
        }
        $validatedData = $validator->validated();

        try {
            $validatedData['password'] = Hash::make($validatedData['password']);
            $user = User::create($validatedData);
            if ($request->role) {
                // Sync role
                $user->roles()->sync([$request->role]);
            }

            $action = $request->f_name . ' user created';
            $additionalData = [
                'is_successful' => true,
                'data' => $user->toArray(),
            ];
            $this->activityLogService->log($action, $additionalData);

            $source = $request->input('source');
            $manifestId = $request->input('manifest_id');

            if ($source === 'manifest.edit' && $manifestId) {
                return redirect()->route('manifest.edit', ['id' => $manifestId])
                    ->with('success', 'User created successfully and returned to Manifest Edit.');
            } elseif ($source === 'manifest.create') {
                return redirect()->route('manifest.create')
                    ->with('success', 'User created successfully and returned to Manifest Create.');
            }

            
            return redirect()->route('users.index')->with('success', 'User created successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error occured' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = User::with(['roles'])->findOrFail($id);
        $authUser = Auth::user();

        if ($authUser->is_super_admin) {
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
        return view('dashboard.users.edit', compact('user', 'roles', 'companies'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'f_name' => 'required|string|max:255',
            'l_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'address' => 'nullable|string',
            'customer_id' => 'nullable|exists:customers,id',
            'role' => 'nullable|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'company_id' => 'nullable|exists:companies,id',
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
                'is_super_admin' => $request->is_super_admin ?? $user->is_super_admin,
                'customer_id' => $request->customer_id,
                'phone' => $request->phone,
                'company_id' => $request->company_id,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            if ($request->role) {
                // Sync role
                $user->roles()->sync([$request->role]);
            }

            $action = $request->f_name . ' user updated';
            $additionalData = [
                'is_successful' => true,
                'data' => $user->toArray(),
            ];
            $this->activityLogService->log($action, $additionalData);
            return redirect()->route('users.index')->with('success', 'User is updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error occured' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_deleted' => 1]);

        $action = $user->f_name . ' user deleted';
        $this->activityLogService->log($action);

        return back()->with('success', 'User is deleted successfully');
    }
}
