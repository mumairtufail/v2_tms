<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerPortalScope
{
    /**
     * Ensure the authenticated customer belongs to the route company.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $customer = Auth::guard('customer')->user();
        $company = app('current.company');

        if (!$customer || $customer->company_id !== $company->id) {
            abort(403, 'You do not have access to this portal.');
        }

        if (!$customer->portal || !$customer->is_active || $customer->is_deleted) {
            Auth::guard('customer')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('portal.login', ['company' => $company->slug])
                ->withErrors(['email' => 'Your portal access has been revoked.']);
        }

        app()->instance('current.customer', $customer);

        return $next($request);
    }
}
