<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PortalCompanyContext
{
    /**
     * Resolve company from route slug without requiring staff authentication.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $company = $request->route('company');

        if (!$company) {
            abort(404, 'Company not found in URL');
        }

        if (is_string($company)) {
            $company = Company::where('slug', $company)->firstOrFail();
        }

        app()->instance('current.company', $company);
        config(['app.current_company_id' => $company->id]);

        return $next($request);
    }
}
