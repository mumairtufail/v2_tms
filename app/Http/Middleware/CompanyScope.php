<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Company;
use Symfony\Component\HttpFoundation\Response;

class CompanyScope
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get company from route
        $company = $request->route('company');
        
        if (!$company) {
            abort(404, 'Company not found in URL');
        }

        // If it's a string (slug), find the company
        if (is_string($company)) {
            $company = Company::where('slug', $company)->firstOrFail();
        }

        // Check if user has access to this company
        if (!$request->user()->is_super_admin && !$request->user()->canAccessCompany($company)) {
            abort(403, 'You do not have access to this company');
        }

        // Store company in app container for global access
        app()->instance('current.company', $company);

        // Set global scope for all models with BelongsToCompany trait
        config(['app.current_company_id' => $company->id]);

        return $next($request);
    }
}
