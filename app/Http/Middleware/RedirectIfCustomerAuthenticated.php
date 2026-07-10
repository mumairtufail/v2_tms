<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfCustomerAuthenticated
{
    /**
     * Redirect authenticated customers away from guest portal pages.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('customer')->check()) {
            $company = $request->route('company');
            $slug = is_string($company) ? $company : $company->slug;

            return redirect()->route('portal.dashboard', ['company' => $slug]);
        }

        return $next($request);
    }
}
