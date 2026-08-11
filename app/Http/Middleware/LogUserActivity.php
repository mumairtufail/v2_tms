<?php

namespace App\Http\Middleware;

use App\Services\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function __construct(
        protected ActivityLog $activityLog
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldLog($request, $response)) {
            $routeName = $request->route()?->getName() ?? $request->path();
            $isSuccessful = $this->isSuccessful($request, $response);

            $this->activityLog->logFromRoute($routeName, [
                'status_code' => $response->getStatusCode(),
                'is_successful' => $isSuccessful,
                'route_parameters' => $request->route()?->parameters() ?? [],
                'input' => $this->sanitizeInput($request->except([
                    'password',
                    'password_confirmation',
                    'current_password',
                    '_token',
                    '_method',
                ])),
            ]);
        }

        return $response;
    }

    protected function shouldLog(Request $request, Response $response): bool
    {
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        if ($response->getStatusCode() >= 500) {
            return false;
        }

        if (!$request->user('web') && !$request->user('customer')) {
            return false;
        }

        if ($this->isExcludedRoute($request)) {
            return false;
        }

        return true;
    }

    protected function isSuccessful(Request $request, Response $response): bool
    {
        if ($response->isClientError()) {
            return false;
        }

        if ($request->session()->has('errors') && $request->session()->get('errors')?->any()) {
            return false;
        }

        if ($request->session()->has('error')) {
            return false;
        }

        return true;
    }

    protected function isExcludedRoute(Request $request): bool
    {
        $routeName = $request->route()?->getName();
        $path = $request->path();

        $excludedRoutes = [
            'login',
            'logout',
            'portal.login',
            'portal.logout',
            'password.email',
            'password.store',
            'password.update',
        ];

        if ($routeName && in_array($routeName, $excludedRoutes, true)) {
            return true;
        }

        if (str_contains($path, '/login') && $request->isMethod('POST')) {
            return true;
        }

        return false;
    }

    protected function sanitizeInput(array $input): array
    {
        return collect($input)
            ->map(function ($value) {
                if (is_array($value)) {
                    return $this->sanitizeInput($value);
                }

                if (is_string($value) && strlen($value) > 500) {
                    return substr($value, 0, 500).'...';
                }

                return $value;
            })
            ->all();
    }
}
