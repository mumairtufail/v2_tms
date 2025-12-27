<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyUsersController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request, $company)
    {
        $filters = $request->only(['search', 'role', 'status']);
        $filters['company_id'] = app('current.company')->id;
        
        $users = $this->userService->getUsers($filters);
        $roles = Role::where('company_id', app('current.company')->id)->get();

        return view('v2.company.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create($company)
    {
        $roles = Role::where('company_id', app('current.company')->id)->get();
        return view('v2.company.users.form', compact('roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request, $company)
    {
        $validated = $request->validate([
            'f_name' => ['required', 'string', 'max:255'],
            'l_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
            'role' => ['required', 'exists:roles,id'],
            'email_notifications' => ['nullable', 'boolean'],
            'two_factor_enabled' => ['nullable', 'boolean'],
        ]);

        $validated['company_id'] = app('current.company')->id;
        $validated['roles'] = [$validated['role']];
        unset($validated['role']);

        try {
            $user = $this->userService->createUser($validated);
            return redirect()
                ->route('v2.users.index', ['company' => app('current.company')->slug])
                ->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('User creation failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user.
     */
    public function show($company, int $id)
    {
        $user = $this->userService->getUserById($id);
        
        if ($user->company_id !== app('current.company')->id) {
            abort(403);
        }

        return view('v2.company.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($company, int $id)
    {
        $user = $this->userService->getUserById($id);
        
        if ($user->company_id !== app('current.company')->id) {
            abort(403);
        }

        $roles = Role::where('company_id', app('current.company')->id)->get();
        return view('v2.company.users.form', compact('user', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $company, int $id)
    {
        $user = $this->userService->getUserById($id);

        if ($user->company_id !== app('current.company')->id) {
            abort(403);
        }

        $validated = $request->validate([
            'f_name' => ['required', 'string', 'max:255'],
            'l_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
            'role' => ['required', 'exists:roles,id'],
            'email_notifications' => ['nullable', 'boolean'],
            'two_factor_enabled' => ['nullable', 'boolean'],
        ]);

        $validated['roles'] = [$validated['role']];
        unset($validated['role']);

        try {
            $user = $this->userService->updateUser($user, $validated);
            return redirect()
                ->route('v2.users.index', ['company' => app('current.company')->slug])
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('User update failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy($company, int $id)
    {
        $user = $this->userService->getUserById($id);

        if ($user->company_id !== app('current.company')->id) {
            abort(403);
        }

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        try {
            $this->userService->deleteUser($user);
            return redirect()
                ->route('v2.users.index', ['company' => app('current.company')->slug])
                ->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('User deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user status.
     */
    public function toggleStatus($company, int $id)
    {
        $user = $this->userService->getUserById($id);

        if ($user->company_id !== app('current.company')->id) {
            abort(403);
        }

        try {
            $this->userService->toggleStatus($user);
            return back()->with('success', 'User status updated successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('User status toggle failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update user status: ' . $e->getMessage());
        }
    }
}
