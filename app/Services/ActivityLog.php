<?php

namespace App\Services;

use App\Models\ActivityLogs;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;
use Illuminate\Support\Facades\Request;

class ActivityLog
{
    /**
     * Log a platform activity.
     */
    public function log(string $action, array $data = [], ?bool $isSuccessful = null): ?ActivityLogs
    {
        try {
            $actor = $this->resolveActor();

            if (!$actor['user_id'] && !$actor['customer_id'] && !($data['allow_guest'] ?? false)) {
                return null;
            }

            unset($data['allow_guest']);

            $payload = array_merge($data, [
                'actor_type' => $actor['type'],
                'actor_name' => $actor['name'],
            ]);

            if (!isset($payload['description'])) {
                $payload['description'] = $action;
            }

            return ActivityLogs::create([
                'user_id' => $actor['user_id'],
                'customer_id' => $actor['customer_id'] ?? ($data['customer_id'] ?? null),
                'company_id' => $this->resolveCompanyId($actor, $data),
                'action' => $action,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'url' => Request::fullUrl(),
                'method' => Request::method(),
                'is_successful' => $isSuccessful ?? ($data['is_successful'] ?? true),
                'data' => $payload,
            ]);
        } catch (\Throwable $e) {
            LaravelLog::error('Failed to write activity log: '.$e->getMessage());

            return null;
        }
    }

    public function logAuth(string $action, array $data = [], bool $isSuccessful = true): ?ActivityLogs
    {
        return $this->log($action, array_merge($data, [
            'category' => 'auth',
            'is_successful' => $isSuccessful,
        ]), $isSuccessful);
    }

    public function logModel(string $action, Model $model, array $data = []): ?ActivityLogs
    {
        return $this->log($action, array_merge($data, [
            'category' => 'model',
            'model_type' => $model::class,
            'model_id' => $model->getKey(),
            'description' => $data['description'] ?? sprintf(
                '%s %s #%s',
                ucfirst($action),
                class_basename($model),
                $model->getKey()
            ),
        ]));
    }

    public function logProfileUpdate(array $changes): ?ActivityLogs
    {
        return $this->log('profile.updated', [
            'category' => 'profile',
            'description' => 'Updated profile',
            'changes' => $changes,
        ]);
    }

    public function logFromRoute(string $routeName, array $context = []): ?ActivityLogs
    {
        return $this->log($routeName, array_merge($context, [
            'category' => 'http',
            'description' => $this->humanizeRouteName($routeName),
            'route' => $routeName,
        ]));
    }

    protected function resolveActor(): array
    {
        if ($user = Auth::guard('web')->user()) {
            /** @var User $user */
            return [
                'type' => 'user',
                'user_id' => $user->id,
                'customer_id' => null,
                'name' => $user->name,
                'company_id' => $user->company_id,
            ];
        }

        if ($customer = Auth::guard('customer')->user()) {
            /** @var Customer $customer */
            return [
                'type' => 'customer',
                'user_id' => null,
                'customer_id' => $customer->id,
                'name' => $customer->name,
                'company_id' => $customer->company_id,
            ];
        }

        return [
            'type' => 'guest',
            'user_id' => null,
            'customer_id' => null,
            'name' => null,
            'company_id' => null,
        ];
    }

    protected function resolveCompanyId(array $actor, array $data = []): ?int
    {
        if ($actor['company_id']) {
            return $actor['company_id'];
        }

        if (isset($data['company_id'])) {
            return (int) $data['company_id'];
        }

        if (app()->bound('current.company')) {
            return app('current.company')->id;
        }

        return config('app.current_company_id');
    }

    protected function humanizeRouteName(string $routeName): string
    {
        $map = [
            'v2.users.store' => 'Created company user',
            'v2.users.update' => 'Updated company user',
            'v2.users.destroy' => 'Deleted company user',
            'v2.roles.store' => 'Created role',
            'v2.roles.update' => 'Updated role',
            'v2.roles.destroy' => 'Deleted role',
            'v2.orders.store' => 'Created order',
            'v2.orders.update' => 'Updated order',
            'v2.orders.destroy' => 'Deleted order',
            'v2.orders.bulk-destroy' => 'Bulk deleted orders',
            'v2.customers.store' => 'Created customer',
            'v2.customers.update' => 'Updated customer',
            'v2.customers.destroy' => 'Deleted customer',
            'v2.carriers.store' => 'Created carrier',
            'v2.carriers.update' => 'Updated carrier',
            'v2.carriers.destroy' => 'Deleted carrier',
            'v2.equipment.store' => 'Created equipment',
            'v2.equipment.update' => 'Updated equipment',
            'v2.equipment.destroy' => 'Deleted equipment',
            'v2.manifests.store' => 'Created manifest',
            'v2.manifests.update' => 'Updated manifest',
            'v2.manifests.destroy' => 'Deleted manifest',
            'v2.manifests.bulk-destroy' => 'Bulk deleted manifests',
            'v2.settings.branding.update' => 'Updated company branding',
            'admin.companies.store' => 'Created company',
            'admin.companies.update' => 'Updated company',
            'admin.companies.destroy' => 'Deleted company',
            'admin.users.store' => 'Created admin user',
            'admin.users.update' => 'Updated admin user',
            'admin.users.destroy' => 'Deleted admin user',
            'portal.settings.profile.update' => 'Customer updated profile',
            'portal.settings.preferences.update' => 'Customer updated settings',
            'portal.logout' => 'Customer logged out',
        ];

        if (isset($map[$routeName])) {
            return $map[$routeName];
        }

        return str($routeName)->replace('.', ' ')->title()->toString();
    }
}
