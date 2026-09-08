<?php

namespace App\Http\Middleware;

use App\Models\Order;
use App\Services\ActivityLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
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
            $description = $this->resolveDescription($request, $routeName, $isSuccessful);

            $context = [
                'status_code' => $response->getStatusCode(),
                'is_successful' => $isSuccessful,
                'order_id' => $this->resolveOrderId($request),
                'route_parameters' => $this->serializeRouteParameters($request->route()?->parameters() ?? []),
                'input' => $this->sanitizeInput($request->except([
                    'password',
                    'password_confirmation',
                    'current_password',
                    '_token',
                    '_method',
                    'stops',
                    'quote_data',
                    'contact_book_entries',
                ])),
            ];

            if ($description !== null) {
                $context['description'] = $description;
            }

            $this->activityLog->logFromRoute($routeName, $context);
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

    protected function resolveDescription(Request $request, string $routeName, bool $isSuccessful): ?string
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
        }

        if ($description === null) {
            return null;
        }

        return $isSuccessful ? $description : 'Failed: '.lcfirst($description);
    }

    protected function serializeRouteParameters(array $parameters): array
    {
        return collect($parameters)
            ->map(function ($value) {
                if ($value instanceof Model) {
                    return [
                        'id' => $value->getKey(),
                        'type' => class_basename($value),
                    ];
                }

                return $value;
            })
            ->all();
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
