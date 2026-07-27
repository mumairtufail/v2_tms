<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DriverCompanyScope
{
    /**
     * Sanctum-token equivalent of CompanyScope: derives the company from the
     * authenticated driver's own company_id instead of a URL segment, since
     * a driver's token is already 1:1 with one company.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->isInactive() || !$user->hasRole('driver')) {
            abort(403, 'This account does not have driver access.');
        }

        $company = $user->company;

        if (!$company || !$company->is_active || $company->is_deleted) {
            abort(403, 'Your company account is inactive.');
        }

        app()->instance('current.company', $company);
        config(['app.current_company_id' => $company->id]);

        return $next($request);
    }
}
