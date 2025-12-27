<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'role', 'status']);
        $users = $this->userService->getUsers($filters);
        $roles = Role::all();

        return view('v2.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::all();
        return view('v2.users.form', compact('roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
            'email_notifications' => ['nullable', 'boolean'],
            'two_factor_enabled' => ['nullable', 'boolean'],
        ]);

        try {
            $user = $this->userService->createUser($validated);
            return redirect()
                ->route('v2.users.show', ['company' => app('current.company')->slug, 'user' => $user->id])
                ->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user.
     */
    public function show(string $company, int $id)
    {
        $user = $this->userService->getUserById($id);
        return view('v2.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(string $company, int $id)
    {
        $user = $this->userService->getUserById($id);
        $roles = Role::all();
        return view('v2.users.form', compact('user', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, string $company, int $id)
    {
        $user = $this->userService->getUserById($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
            'email_notifications' => ['nullable', 'boolean'],
            'two_factor_enabled' => ['nullable', 'boolean'],
        ]);

        try {
            $user = $this->userService->updateUser($user, $validated);
            return redirect()
                ->route('v2.users.show', ['company' => app('current.company')->slug, 'user' => $user->id])
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(string $company, int $id)
    {
        $user = $this->userService->getUserById($id);

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
            return back()->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user status.
     */
    public function toggleStatus(string $company, int $id)
    {
        $user = $this->userService->getUserById($id);

        try {
            $this->userService->toggleStatus($user);
            return back()->with('success', 'User status updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update user status: ' . $e->getMessage());
        }
    }
}
