<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Address autocomplete fires on every keystroke across every shipper/consignee
        // field on a multi-stop order, plus the customer search box on the same page -
        // that traffic was sharing the generic 60/min 'api' budget with every other /api
        // route, so an active data-entry session could exhaust it and "search" would go
        // quiet with no results. Google Places gets its own, much larger budget instead
        // of competing with everything else under /api (routes/api.php excludes it from
        // 'throttle:api' and applies this one directly).
        RateLimiter::for('google-places', function (Request $request) {
            return Limit::perMinute(300)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // V2 Routes with company-based multi-tenancy
            Route::middleware('web')
                ->group(base_path('routes/v2.php'));

            // Customer portal routes
            Route::middleware('web')
                ->group(base_path('routes/portal.php'));
        });
    }
}
