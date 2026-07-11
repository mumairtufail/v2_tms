<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Log out users who were deactivated after logging in.
     * Impersonation sessions are exempt so admins can still
     * inspect an inactive user's account.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !$request->session()->has('impersonating_original_id')) {
            if (Auth::user()->isInactive()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Your account has been deactivated. Please contact your administrator.');
            }
        }

        return $next($request);
    }
}
