<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Services\ActivityLog;
use App\Support\EmailRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            ->where('is_deleted', false)
            ->where('is_super_admin', false)
            ->whereHas('roles', function ($q) {
                $q->whereRaw('LOWER(roles.name) in (?, ?)', ['admin', 'company_admin']);
            });

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

        $users = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Get companies for the company filter
        $companies = Company::where('is_deleted', false)
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('v2.admin.users.index', compact('users', 'companies'));
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
            'email' => ['required', ...EmailRules::deliverable(), 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'phone' => 'nullable|string|max:20',
            'company_id' => 'required|exists:companies,id',
            'status' => 'required|in:active,inactive',
            'role' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Please check the form for errors.')
                ->withErrors($validator)
                ->withInput($request->except(['password', 'password_confirmation']));
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
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'phone' => 'nullable|string|max:20',
            'company_id' => 'required|exists:companies,id',
            'status' => 'required|in:active,inactive',
            'role' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Please check the form for errors.')
                ->withErrors($validator)
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        try {
            $user->update([
                'f_name' => $request->f_name,
                'l_name' => $request->l_name,
                'name' => $request->f_name . ' ' . $request->l_name,
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
     * Impersonate a company user as super admin.
     */
    public function impersonate(User $user)
    {
        // Captured before Auth::login swaps the session, otherwise the audit
        // trail attributes the action to the account being impersonated.
        $actor = auth()->user();

        if ($user->is_super_admin) {
            app(ActivityLog::class)->logImpersonation(
                'impersonation.start.denied',
                $actor,
                $user,
                'Attempted to impersonate a super admin and was refused',
                ['is_successful' => false, 'reason' => 'target is a super admin']
            );

            return redirect()->back()->with('error', 'You cannot impersonate a super admin.');
        }

        if (!$user->company) {
            app(ActivityLog::class)->logImpersonation(
                'impersonation.start.denied',
                $actor,
                $user,
                'Attempted to impersonate a user with no company and was refused',
                ['is_successful' => false, 'reason' => 'target has no company']
            );

            return redirect()->back()->with('error', 'This user has no company assigned.');
        }

        app(ActivityLog::class)->logImpersonation(
            'impersonation.start',
            $actor,
            $user,
            sprintf('%s started impersonating %s', $actor->name, $user->name)
        );

        session(['impersonating_original_id' => $actor->id]);

        Auth::login($user);

        return redirect()->route('v2.dashboard', ['company' => $user->company->slug])
            ->with('success', 'You are now logged in as ' . $user->name . '.');
    }

    /**
     * Stop impersonating and return to the original super admin account.
     */
    public function stopImpersonating(Request $request)
    {
        // The account currently being worn, recorded before the session flips
        // back so the entry names both sides of the handover.
        $impersonatedUser = auth()->user();

        $originalId = session()->pull('impersonating_original_id');

        if (!$originalId) {
            return redirect()->route('admin.dashboard');
        }

        $originalUser = User::find($originalId);

        if (!$originalUser || !$originalUser->is_super_admin) {
            if ($originalUser) {
                app(ActivityLog::class)->logImpersonation(
                    'impersonation.stop.failed',
                    $originalUser,
                    $impersonatedUser,
                    'Could not return from impersonation; the original account is no longer a super admin',
                    ['is_successful' => false]
                );
            }

            Auth::logout();
            return redirect()->route('login')->with('error', 'Session expired. Please log in again.');
        }

        app(ActivityLog::class)->logImpersonation(
            'impersonation.stop',
            $originalUser,
            $impersonatedUser,
            sprintf(
                '%s stopped impersonating %s',
                $originalUser->name,
                $impersonatedUser->name ?? 'a user'
            )
        );

        Auth::login($originalUser);

        return redirect()->route('admin.users.index')
            ->with('success', 'You have returned to your super admin account.');
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
