<?php

namespace App\Providers;

use App\Services\ActivityLog;
use App\Services\ActivityLogWriter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Both are singletons so one buffer collects every entry produced
        // during a request and leaves in a single insert.
        $this->app->singleton(ActivityLogWriter::class, function ($app) {
            return new ActivityLogWriter(
                connection: config('audit.connection'),
                bufferLimit: (int) config('audit.buffer_limit', 100),
                deferred: (bool) config('audit.deferred_writes', true),
            );
        });

        $this->app->singleton(ActivityLog::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
