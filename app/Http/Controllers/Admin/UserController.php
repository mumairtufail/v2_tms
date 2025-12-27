<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of all users across all companies (Admin view).
     */
    public function index(Request $request)
    {
        $query = User::with(['company', 'roles'])
            ->where('is_deleted', false);

        // Search by name or email
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('f_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('l_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by company
        if ($request->filled('company')) {
            $query->where('company_id', $request->company);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('roles.id', $request->role);
            });
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Get companies and roles for filters
        $companies = Company::where('is_deleted', false)
            ->orderBy('name')
            ->pluck('name', 'id');
        
        $roles = Role::orderBy('name')->pluck('name', 'id');

        return view('v2.admin.users.index', compact('users', 'companies', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $companies = Company::where('is_deleted', false)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');
        
        $roles = Role::orderBy('name')->get();

        return view('v2.admin.users.form', compact('companies', 'roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required|string|max:255',
            'l_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone' => 'nullable|string|max:20',
            'company_id' => 'required|exists:companies,id',
            'status' => 'required|in:active,inactive',
            'role' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Please check the form for errors.')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = User::create([
                'f_name' => $request->f_name,
                'l_name' => $request->l_name,
                'name' => $request->f_name . ' ' . $request->l_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'company_id' => $request->company_id,
                'status' => $request->status,
                'is_active' => $request->status === 'active',
            ]);

            // Sync role (single)
            if ($request->filled('role')) {
                $user->roles()->sync([$request->role]);
            }

            return redirect()->route('admin.users.index')
                ->with('success', 'User created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['company', 'roles']);
        return view('v2.admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $user->load('roles');
        
        $companies = Company::where('is_deleted', false)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');
        
        $roles = Role::orderBy('name')->get();

        return view('v2.admin.users.form', compact('user', 'companies', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required|string|max:255',
            'l_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'phone' => 'nullable|string|max:20',
            'company_id' => 'required|exists:companies,id',
            'status' => 'required|in:active,inactive',
            'role' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Please check the form for errors.')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user->update([
                'f_name' => $request->f_name,
                'l_name' => $request->l_name,
                'name' => $request->f_name . ' ' . $request->l_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'company_id' => $request->company_id,
                'status' => $request->status,
                'is_active' => $request->status === 'active',
            ]);

            // Update password only if provided
            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            // Sync role (single)
            $user->roles()->sync($request->filled('role') ? [$request->role] : []);

            return redirect()->route('admin.users.index')
                ->with('success', 'User updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account.');
        }

        try {
            // Soft delete
            $user->update(['is_deleted' => true]);
            
            return redirect()->route('admin.users.index')
                ->with('success', 'User deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}
