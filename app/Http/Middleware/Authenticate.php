<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        if ($request->routeIs('portal.*')) {
            $company = $request->route('company');
            $slug = is_object($company) ? $company->slug : $company;

            return route('portal.login', ['company' => $slug]);
        }

        return route('login');
    }
}
