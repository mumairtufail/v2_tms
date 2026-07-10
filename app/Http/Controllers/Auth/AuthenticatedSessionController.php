<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Services\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        // If 2FA is enabled and confirmed, challenge before granting access
        if ($user->two_factor_enabled && $user->two_factor_confirmed_at) {
            Auth::logout();
            $request->session()->put('2fa.user_id', $user->id);
            $request->session()->put('2fa.remember', $request->boolean('remember'));
            return redirect()->route('two-factor.challenge');
        }

        $request->session()->regenerate();

        $rememberExpiry = $request->boolean('remember') ? now()->addDays(30) : null;
        $user->update([
            'last_login_at' => now(),
            'remember_token_expires_at' => $rememberExpiry,
        ]);

        if ($user->is_super_admin) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->company) {
            return redirect()->route('v2.dashboard', ['company' => $user->company->slug]);
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            app(ActivityLog::class)->logAuth('auth.logout', [
                'description' => 'Staff user logged out',
                'email' => $user->email,
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
