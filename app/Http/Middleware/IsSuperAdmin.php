<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->is_super_admin) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Super Admin access required.'], 403);
            }

            $user = $request->user();
            if ($user && $user->company?->slug) {
                return redirect()->route('v2.dashboard', ['company' => $user->company->slug])
                    ->with('error', 'Unauthorized. Super Admin access required.');
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
