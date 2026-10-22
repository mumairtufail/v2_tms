<?php

namespace App\Http\Middleware;

use App\Models\Order;
use App\Services\ActivityLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records every authenticated request that reaches the application - reads as
 * well as writes, failures as well as successes. What is skipped is driven by
 * config/audit.php so the noise floor can be tuned without touching code.
 */
class LogUserActivity
{
    public function __construct(
        protected ActivityLog $activityLog
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);

        $response = $next($request);

        if ($this->shouldLog($request, $response)) {
            $this->record($request, $response, $startedAt);
        }

        return $response;
    }

    protected function record(Request $request, Response $response, float $startedAt): void
    {
        $routeName = $request->route()?->getName() ?? $request->path();
        $statusCode = $response->getStatusCode();
        $isSuccessful = $this->isSuccessful($request, $response);
        $isRead = $this->isRead($request);
        $description = $this->resolveDescription($request, $routeName, $isSuccessful, $isRead);

        $context = [
            'category' => $isRead ? 'read' : 'http',
            'severity' => $this->resolveSeverity($statusCode, $isSuccessful),
            'status_code' => $statusCode,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'is_successful' => $isSuccessful,
            'order_id' => $this->resolveOrderId($request),
            'route_parameters' => $this->serializeRouteParameters($request->route()?->parameters() ?? []),
        ];

        // A read has no body worth keeping; its query string is the payload.
        if ($isRead) {
            // An empty filter form submits every field blank, which used to be
            // stored as {"to":null,"from":null,...} and shown as if it meant something.
            $query = array_filter($request->query(), fn ($value) => $value !== null && $value !== '' && $value !== []);
            if (! empty($query)) {
                $context['query'] = $query;
            }
        } else {
            $context['input'] = $request->except(array_merge(
                config('audit.strip_input', []),
                config('audit.redact', []),
            ));
        }

        if (! $isSuccessful) {
            $context['validation_errors'] = $this->resolveValidationErrors($request);
        }

        if ($description !== null) {
            $context['description'] = $description;
        }

        $this->activityLog->logFromRoute($routeName, $context);
    }

    protected function shouldLog(Request $request, Response $response): bool
    {
        if (! config('audit.enabled', true)) {
            return false;
        }

        if (! $request->user('web') && ! $request->user('customer')) {
            return false;
        }

        if ($this->isExcludedRoute($request)) {
            return false;
        }

        if ($this->isRead($request)) {
            // Reads are opt-in as a body of traffic, and some pages are hit
            // often enough that logging them buries everything else.
            if (! config('audit.log_reads', true)) {
                return false;
            }

            if ($this->matchesAny($request->path(), config('audit.excluded_read_paths', []))) {
                return false;
            }
        }

        return true;
    }

    protected function isRead(Request $request): bool
    {
        return in_array($request->method(), ['GET', 'HEAD'], true);
    }

    protected function isSuccessful(Request $request, Response $response): bool
    {
        if ($response->getStatusCode() >= 400) {
            return false;
        }

        if (! $request->hasSession()) {
            return true;
        }

        if ($request->session()->has('errors') && $request->session()->get('errors')?->any()) {
            return false;
        }

        if ($request->session()->has('error')) {
            return false;
        }

        return true;
    }

    protected function resolveSeverity(int $statusCode, bool $isSuccessful): string
    {
        return match (true) {
            $statusCode >= 500 => ActivityLog::SEVERITY_ERROR,
            $statusCode === 401, $statusCode === 403 => ActivityLog::SEVERITY_WARNING,
            $statusCode >= 400 => ActivityLog::SEVERITY_NOTICE,
            ! $isSuccessful => ActivityLog::SEVERITY_NOTICE,
            default => ActivityLog::SEVERITY_INFO,
        };
    }

    /**
     * Sign-in and sign-out are logged by the auth classes themselves, which see
     * the credentials and the outcome; logging them here as well would double
     * every entry.
     */
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
            // Logged by UserController through logImpersonation, which still
            // knows who the real actor is. By the time this middleware runs the
            // session has already been swapped, so an entry here would name the
            // impersonated user as the one who started the impersonation.
            'admin.users.impersonate',
            'admin.impersonate.stop',
        ];

        if ($routeName && in_array($routeName, $excludedRoutes, true)) {
            return true;
        }

        // POST /login is an unnamed route, and $request->path() has no leading
        // slash, so matching on '/login' never fired and every sign-in produced
        // a duplicate entry next to the one the auth classes write.
        if ($request->isMethod('POST') && $request->is('login', '*/login', 'logout', '*/logout')) {
            return true;
        }

        return $this->matchesAny($path, config('audit.excluded_paths', []));
    }

    protected function matchesAny(string $path, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if ($path === $pattern || fnmatch($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    protected function resolveOrderId(Request $request): ?int
    {
        if ($request->attributes->has('activity_order_id')) {
            return (int) $request->attributes->get('activity_order_id');
        }

        $order = $request->route('order');

        if ($order instanceof Order) {
            return $order->id;
        }

        if (is_numeric($order)) {
            return (int) $order;
        }

        return null;
    }

    protected function resolveValidationErrors(Request $request): array
    {
        if (! $request->hasSession()) {
            return [];
        }

        $errors = $request->session()->get('errors');

        if (! $errors || ! $errors->any()) {
            return [];
        }

        // Field names and messages only - the rejected values may be secrets.
        return array_keys($errors->getBag('default')->getMessages());
    }

    protected function resolveDescription(Request $request, string $routeName, bool $isSuccessful, bool $isRead): ?string
    {
        $description = null;

        if ($request->attributes->has('activity_description')) {
            $description = (string) $request->attributes->get('activity_description');
        } elseif (in_array($routeName, ['v2.orders.update', 'portal.orders.update'], true)) {
            if ($request->input('save_as_draft') === '1') {
                $description = 'Saved the order as a draft';
            } elseif ($request->input('submission_mode') === 'quote') {
                $description = 'Quoted the order';
            } elseif ($request->input('submission_mode') === 'new') {
                $description = 'Submitted the order';
            } else {
                $description = 'Updated the order';
            }
        } elseif (in_array($routeName, ['v2.orders.store', 'portal.orders.store'], true)) {
            $description = 'Added the order';
        } elseif ($routeName === 'v2.orders.destroy') {
            $description = 'Deleted the order';
        } elseif ($isRead) {
            $description = $this->describeRead($routeName);
        }

        if ($description === null) {
            return null;
        }

        return $isSuccessful ? $description : 'Failed: '.lcfirst($description);
    }

    /**
     * Turn "v2.customers.show" into "Viewed customer details" rather than the
     * title-cased route name, which reads like machine output.
     */
    protected function describeRead(string $routeName): ?string
    {
        $segments = explode('.', $routeName);
        $verb = array_pop($segments);
        $subject = str_replace(['-', '_'], ' ', (string) array_pop($segments));

        if ($subject === '') {
            return null;
        }

        return match ($verb) {
            'index' => 'Viewed the '.$subject.' list',
            'show' => 'Viewed '.str($subject)->singular()->toString().' details',
            'edit' => 'Opened the '.str($subject)->singular()->toString().' edit form',
            'create' => 'Opened the new '.str($subject)->singular()->toString().' form',
            'export', 'download' => 'Exported '.$subject,
            'print' => 'Printed '.$subject,
            default => null,
        };
    }

    protected function serializeRouteParameters(array $parameters): array
    {
        return collect($parameters)
            ->map(function ($value) {
                if ($value instanceof Model) {
                    return [
                        'id' => $value->getKey(),
                        'type' => class_basename($value),
                        'class' => $value::class,
                    ];
                }

                return $value;
            })
            ->all();
    }
}
