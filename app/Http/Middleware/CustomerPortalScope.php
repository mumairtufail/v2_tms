<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerPortalScope
{
    /**
     * Ensure the signed-in person belongs to the route company and still has portal access.
     * Binds the person (current.portal_contact) and their customer (current.customer).
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\CustomerContact|null $contact */
        $contact = Auth::guard('customer')->user();
        $company = app('current.company');

        if (!$contact || (int) $contact->company_id !== (int) $company->id) {
            abort(403, 'You do not have access to this portal.');
        }

        if (!$contact->canUsePortal()) {
            Auth::guard('customer')->logout();

            if (! Auth::guard('web')->check()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('portal.login', ['company' => $company->slug])
                ->withErrors(['email' => 'Your portal access has been revoked.']);
        }

        app()->instance('current.portal_contact', $contact);
        app()->instance('current.customer', $contact->customer);

        return $next($request);
    }
}
