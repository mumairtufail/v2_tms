<?php

namespace App\Services;

use App\Models\ActivityLogs;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;
use Illuminate\Support\Facades\Request;
use Throwable;

/**
 * Writes the audit trail.
 *
 * Every entry records who acted, who they were acting *as* if impersonating,
 * what happened, whether it worked, and from where. Writes never throw: a
 * failure here must not take down the request it was describing.
 */
class ActivityLog
{
    public const SEVERITY_INFO = 'info';
    public const SEVERITY_NOTICE = 'notice';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_ERROR = 'error';
    public const SEVERITY_CRITICAL = 'critical';

    /**
     * Keys that carry engineer-facing detail. They are moved out of the shown
     * payload into the technical column, which only a super admin can read.
     */
    protected const TECHNICAL_KEYS = [
        'exception',
        'exception_message',
        'exception_chain',
        'file',
        'trace',
        'sql',
        'sql_bindings',
        'headers',
        'response_body',
        'previous',
        'memory_mb',
        'php_version',
        'controller_action',
        'stack',
    ];

    protected ?string $requestId = null;

    public function __construct(
        protected ActivityLogWriter $writer
    ) {}

    /**
     * Log a platform activity.
     *
     * Returns null rather than a model: rows are buffered and written after the
     * response is sent, so there is nothing to hand back at call time. Callers
     * that genuinely need the persisted row should use logNow().
     */
    public function log(string $action, array $data = [], ?bool $isSuccessful = null): ?ActivityLogs
    {
        $row = $this->buildRow($action, $data, $isSuccessful);

        if ($row === null) {
            return null;
        }

        // Buffered entries are lost if the process dies before terminating -
        // a fatal, a timeout, an OOM kill. Those are exactly the moments an
        // error or an impersonation entry matters most, so the serious
        // severities are written immediately and only routine traffic waits.
        if (in_array($row['severity'], [self::SEVERITY_ERROR, self::SEVERITY_CRITICAL], true)) {
            $this->writer->writeNow($row);

            return null;
        }

        $this->writer->queue($row);

        return null;
    }

    /**
     * Write one entry immediately and return it, for the few places that need
     * the row to exist before the response is built.
     */
    public function logNow(string $action, array $data = [], ?bool $isSuccessful = null): ?ActivityLogs
    {
        $row = $this->buildRow($action, $data, $isSuccessful);

        if ($row === null) {
            return null;
        }

        $id = $this->writer->writeNow($row);

        return $id ? ActivityLogs::find($id) : null;
    }

    /**
     * Turn a call into the row that will be inserted, or null when there is
     * nothing to record. Never throws.
     *
     * @return array<string, mixed>|null
     */
    protected function buildRow(string $action, array $data = [], ?bool $isSuccessful = null): ?array
    {
        if (! config('audit.enabled', true)) {
            return null;
        }

        try {
            $actor = $this->resolveActor();

            if (! $actor['user_id'] && ! $actor['customer_id'] && ! ($data['allow_guest'] ?? false)) {
                return null;
            }

            unset($data['allow_guest']);

            $successful = $isSuccessful ?? ($data['is_successful'] ?? true);
            $severity = $data['severity'] ?? ($successful ? self::SEVERITY_INFO : self::SEVERITY_WARNING);
            $category = $data['category'] ?? 'general';
            $subject = $this->resolveSubject($data);

            // The engineer-facing half is split off here and never reaches the
            // payload that company users are shown.
            $technical = $this->extractTechnical($data);

            $payload = $this->sanitize(array_merge($data, [
                'actor_type' => $actor['type'],
                'actor_name' => $actor['name'],
                'actor_email' => $actor['email'],
            ]));

            if (! isset($payload['description'])) {
                $payload['description'] = $action;
            }

            // The impersonator is recorded in its own columns, not folded into
            // the description. That keeps one stored description that reads
            // correctly for a company user, while the super admin view composes
            // the "(as X, impersonated by Y)" wording at render time.
            //
            // Severity is deliberately not raised here: it would paint every
            // routine page view during a support session as "Attention". The
            // noteworthy scope already finds these rows via impersonator_id.
            if ($actor['impersonator_id']) {
                $payload['impersonator_name'] = $actor['impersonator_name'];
                $payload['impersonated'] = true;
            }

            $now = now();

            return [
                'user_id' => $actor['user_id'],
                'impersonator_id' => $actor['impersonator_id'],
                'actor_name' => $this->truncate($actor['name'], 255),
                'actor_email' => $this->truncate($actor['email'], 255),
                'impersonator_name' => $this->truncate($actor['impersonator_name'], 255),
                'customer_id' => $actor['customer_id'] ?? ($data['customer_id'] ?? null),
                'company_id' => $this->resolveCompanyId($actor, $data),
                'order_id' => $this->resolveOrderId($data),
                'session_id' => $this->resolveSessionId(),
                'request_id' => $this->requestId(),
                'subject_type' => $this->truncate($subject['type'], 255),
                'subject_id' => $subject['id'],
                'action' => $this->truncate($action, 255),
                'category' => $this->truncate($category, 32),
                'severity' => $severity,
                'ip_address' => $this->truncate($this->resolveIp(), 45),
                'user_agent' => $this->truncate(Request::userAgent(), 500),
                'url' => $this->truncate($this->currentUrl(), 2000),
                'method' => $this->truncate(Request::method(), 10),
                'status_code' => isset($data['status_code']) ? (int) $data['status_code'] : null,
                'duration_ms' => isset($data['duration_ms']) ? (int) $data['duration_ms'] : null,
                'is_successful' => $successful,
                'data' => json_encode($payload),
                'technical' => $technical ? json_encode($technical) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        } catch (Throwable $e) {
            LaravelLog::error('Failed to build activity log entry: '.$e->getMessage(), [
                'action' => $action,
                'exception' => $e,
            ]);

            return null;
        }
    }

    public function logAuth(string $action, array $data = [], bool $isSuccessful = true): ?ActivityLogs
    {
        return $this->log($action, array_merge($data, [
            'category' => 'auth',
            'is_successful' => $isSuccessful,
            // A failed sign-in is the signal worth keeping; say so loudly.
            'severity' => $data['severity'] ?? ($isSuccessful ? self::SEVERITY_INFO : self::SEVERITY_WARNING),
            'allow_guest' => $data['allow_guest'] ?? true,
        ]), $isSuccessful);
    }

    public function logModel(string $action, Model $model, array $data = []): ?ActivityLogs
    {
        return $this->log($action, array_merge($data, [
            'category' => $data['category'] ?? 'model',
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

    /**
     * A permission or tenancy check turned the request away. Always recorded,
     * always a warning - repeated entries here are how probing shows up.
     */
    public function logDenied(string $action, string $description, array $data = []): ?ActivityLogs
    {
        return $this->log($action, array_merge($data, [
            'category' => 'authorization',
            'description' => $description,
            'severity' => self::SEVERITY_WARNING,
            'is_successful' => false,
            'allow_guest' => true,
        ]), false);
    }

    /**
     * An unhandled failure. Kept separate from a plain "failed" entry so the
     * two can be filtered apart.
     */
    public function logError(string $action, string $description, array $data = []): ?ActivityLogs
    {
        return $this->log($action, array_merge($data, [
            'category' => 'error',
            'description' => $description,
            'severity' => $data['severity'] ?? self::SEVERITY_ERROR,
            'is_successful' => false,
            'allow_guest' => true,
        ]), false);
    }

    /**
     * Impersonation start/stop. The actor is passed in explicitly because at
     * the moment this is called the session may already have been swapped.
     */
    public function logImpersonation(string $action, User $actor, ?User $target, string $description, array $data = []): ?ActivityLogs
    {
        if (! config('audit.enabled', true)) {
            return null;
        }

        try {
            $now = now();

            // Written immediately rather than buffered: an impersonation event
            // must survive even if the request dies before it terminates.
            $id = $this->writer->writeNow([
                'user_id' => $actor->id,
                'impersonator_id' => $actor->id,
                'actor_name' => $this->truncate($actor->name, 255),
                'actor_email' => $this->truncate($actor->email, 255),
                'impersonator_name' => $this->truncate($actor->name, 255),
                'customer_id' => null,
                'company_id' => $target?->company_id ?? $actor->company_id,
                'order_id' => null,
                'session_id' => $this->resolveSessionId(),
                'request_id' => $this->requestId(),
                'subject_type' => $target ? $target::class : null,
                'subject_id' => $target?->getKey(),
                'action' => $action,
                'category' => 'impersonation',
                'severity' => self::SEVERITY_CRITICAL,
                'ip_address' => $this->truncate($this->resolveIp(), 45),
                'user_agent' => $this->truncate(Request::userAgent(), 500),
                'url' => $this->truncate($this->currentUrl(), 2000),
                'method' => $this->truncate(Request::method(), 10),
                'status_code' => null,
                'duration_ms' => null,
                'is_successful' => $data['is_successful'] ?? true,
                'data' => json_encode($this->sanitize(array_merge($data, [
                    'description' => $description,
                    'category' => 'impersonation',
                    'actor_type' => 'user',
                    'actor_name' => $actor->name,
                    'actor_email' => $actor->email,
                    'impersonator_name' => $actor->name,
                    'target_user_id' => $target?->id,
                    'target_user_name' => $target?->name,
                    'target_user_email' => $target?->email,
                ]))),
                'technical' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $id ? ActivityLogs::find($id) : null;
        } catch (Throwable $e) {
            LaravelLog::error('Failed to write impersonation log: '.$e->getMessage(), ['exception' => $e]);

            return null;
        }
    }

    public function logFromRoute(string $routeName, array $context = []): ?ActivityLogs
    {
        $isSuccessful = $context['is_successful'] ?? true;
        $description = $context['description'] ?? $this->humanizeRouteName($routeName);

        if (! $isSuccessful && ! isset($context['description'])) {
            $description = 'Failed: '.lcfirst($description);
        }

        return $this->log($routeName, array_merge($context, [
            'category' => $context['category'] ?? 'http',
            'description' => $description,
            'route' => $routeName,
            'is_successful' => $isSuccessful,
        ]), $isSuccessful);
    }

    /**
     * The acting identity, plus the real human behind it when the session is
     * an impersonation. Session state is the source of truth here because
     * Auth has already been swapped to the target by the time we run.
     */
    protected function resolveActor(): array
    {
        if ($user = Auth::guard('web')->user()) {
            /** @var User $user */
            $impersonator = $this->resolveImpersonator($user);

            return [
                'type' => $impersonator ? 'user:impersonated' : 'user',
                'user_id' => $user->id,
                'customer_id' => null,
                'name' => $user->name,
                'email' => $user->email ?? null,
                'company_id' => $user->company_id,
                'impersonator_id' => $impersonator?->id,
                'impersonator_name' => $impersonator?->name,
            ];
        }

        if ($contact = Auth::guard('customer')->user()) {
            /** @var \App\Models\CustomerContact $contact */
            return [
                'type' => 'customer',
                'user_id' => null,
                'customer_id' => $contact->customer_id,
                'name' => $contact->name,
                'email' => $contact->email ?? null,
                'company_id' => $contact->company_id,
                'impersonator_id' => null,
                'impersonator_name' => null,
            ];
        }

        return [
            'type' => 'guest',
            'user_id' => null,
            'customer_id' => null,
            'name' => null,
            'email' => null,
            'company_id' => null,
            'impersonator_id' => null,
            'impersonator_name' => null,
        ];
    }

    /**
     * The super admin behind an impersonated session, if any. Resolved from the
     * session key set when impersonation began.
     */
    protected function resolveImpersonator(User $current): ?User
    {
        $session = $this->currentSession();

        if (! $session) {
            return null;
        }

        try {
            $originalId = $session->get('impersonating_original_id');

            if (! $originalId || (int) $originalId === (int) $current->id) {
                return null;
            }

            return User::find($originalId);
        } catch (Throwable) {
            return null;
        }
    }

    protected function resolveSessionId(): ?string
    {
        $session = $this->currentSession();

        if (! $session) {
            return null;
        }

        try {
            // Hashed: the raw id is a live credential, and this only needs to
            // correlate rows, not authenticate anything.
            return substr(hash('sha256', $session->getId()), 0, 32);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * The active session store, or null when there isn't one.
     *
     * Resolved from the container rather than off the request, because queued
     * jobs, console commands and the exception handler all reach this class
     * without a request bound to a session.
     */
    protected function currentSession(): ?\Illuminate\Contracts\Session\Session
    {
        try {
            if (! app()->bound('session.store')) {
                return null;
            }

            $store = app('session.store');

            return $store->isStarted() ? $store : null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * The client address. Accuracy here depends on TrustProxies being told
     * which proxies sit in front of the app - see the TRUSTED_PROXIES env var.
     */
    protected function resolveIp(): ?string
    {
        try {
            return Request::ip();
        } catch (Throwable) {
            return null;
        }
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

    protected function resolveOrderId(array $data): ?int
    {
        if (! empty($data['order_id'])) {
            return (int) $data['order_id'];
        }

        $order = $data['route_parameters']['order'] ?? null;

        if (is_array($order) && isset($order['id'])) {
            return (int) $order['id'];
        }

        if (is_numeric($order)) {
            return (int) $order;
        }

        return null;
    }

    /**
     * The record the action was about.
     *
     * @return array{type: ?string, id: ?int}
     */
    protected function resolveSubject(array $data): array
    {
        if (! empty($data['model_type'])) {
            return [
                'type' => $data['model_type'],
                'id' => isset($data['model_id']) ? (int) $data['model_id'] : null,
            ];
        }

        if (! empty($data['subject_type'])) {
            return [
                'type' => $data['subject_type'],
                'id' => isset($data['subject_id']) ? (int) $data['subject_id'] : null,
            ];
        }

        // Fall back to the first model bound to the route - for most routes
        // that is exactly the record being acted on.
        foreach ($data['route_parameters'] ?? [] as $value) {
            if (is_array($value) && isset($value['class'], $value['id'])) {
                return ['type' => $value['class'], 'id' => (int) $value['id']];
            }
        }

        return ['type' => null, 'id' => null];
    }

    /**
     * Pull the engineer-facing keys out of the payload and enrich them with
     * what only this moment knows: the exception chain, the controller, the
     * failing SQL. The keys are removed from $data by reference so they cannot
     * leak into the payload a company user sees.
     */
    protected function extractTechnical(array &$data): ?array
    {
        $technical = [];

        foreach (self::TECHNICAL_KEYS as $key) {
            if (array_key_exists($key, $data)) {
                $technical[$key] = $data[$key];
                unset($data[$key]);
            }
        }

        if (isset($data['throwable']) && $data['throwable'] instanceof Throwable) {
            $technical = array_merge($technical, $this->describeThrowable($data['throwable']));
            unset($data['throwable']);
        }

        if ($technical === []) {
            return null;
        }

        try {
            $technical['controller_action'] ??= Request::route()?->getActionName();
            $technical['php_version'] ??= PHP_VERSION;
            $technical['memory_mb'] ??= round(memory_get_peak_usage(true) / 1048576, 1);
            $technical['headers'] ??= $this->safeHeaders();
        } catch (Throwable) {
            // Enrichment is best effort; the core detail is already captured.
        }

        return $this->sanitize($technical);
    }

    /**
     * The exception, its causes, and a trimmed stack. Full traces are the
     * largest thing on the row, so they are capped rather than stored whole.
     */
    protected function describeThrowable(Throwable $e): array
    {
        $frames = (int) config('audit.trace_frames', 20);

        $chain = [];
        $current = $e->getPrevious();
        $depth = 0;

        while ($current !== null && $depth < 5) {
            $chain[] = [
                'class' => $current::class,
                'message' => $current->getMessage(),
                'at' => $current->getFile().':'.$current->getLine(),
            ];
            $current = $current->getPrevious();
            $depth++;
        }

        $technical = [
            'exception' => $e::class,
            'exception_message' => $e->getMessage(),
            'file' => $e->getFile().':'.$e->getLine(),
            'code' => $e->getCode(),
            'trace' => collect(explode("\n", $e->getTraceAsString()))
                ->take($frames)
                ->implode("\n"),
        ];

        if ($chain !== []) {
            $technical['exception_chain'] = $chain;
        }

        // A query failure is the one case where the statement itself is the
        // answer, so it is lifted out of the generic message.
        if ($e instanceof \Illuminate\Database\QueryException) {
            $technical['sql'] = $e->getSql();
            $technical['sql_bindings'] = $e->getBindings();
        }

        return $technical;
    }

    /**
     * Request headers with credentials removed.
     */
    protected function safeHeaders(): array
    {
        $drop = ['cookie', 'authorization', 'x-csrf-token', 'x-xsrf-token', 'proxy-authorization'];

        return collect(Request::headers()->all())
            ->reject(fn ($value, $key) => in_array(strtolower($key), $drop, true))
            ->map(fn ($value) => is_array($value) ? implode(', ', $value) : $value)
            ->all();
    }

    /**
     * One id shared by every entry written during this request, so a page load
     * and the record changes and errors it caused can be read as one event.
     */
    protected function requestId(): string
    {
        return $this->requestId ??= (string) \Illuminate\Support\Str::uuid();
    }

    protected function currentUrl(): ?string
    {
        try {
            return Request::fullUrl();
        } catch (Throwable) {
            return null;
        }
    }

    protected function raiseSeverity(string $current, string $floor): string
    {
        $order = [
            self::SEVERITY_INFO => 0,
            self::SEVERITY_NOTICE => 1,
            self::SEVERITY_WARNING => 2,
            self::SEVERITY_ERROR => 3,
            self::SEVERITY_CRITICAL => 4,
        ];

        return ($order[$current] ?? 0) >= ($order[$floor] ?? 0) ? $current : $floor;
    }

    /**
     * Strip secrets and cap size before anything reaches the database.
     */
    public function sanitize(array $payload, int $depth = 0): array
    {
        $redact = array_map('strtolower', config('audit.redact', []));
        $maxDepth = (int) config('audit.max_array_depth', 6);
        $maxLength = (int) config('audit.max_string_length', 500);

        if ($depth >= $maxDepth) {
            return ['truncated' => 'max depth reached'];
        }

        $out = [];

        foreach ($payload as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), $redact, true)) {
                $out[$key] = '[redacted]';

                continue;
            }

            if (is_array($value)) {
                $out[$key] = $this->sanitize($value, $depth + 1);

                continue;
            }

            if ($value instanceof Model) {
                $out[$key] = ['id' => $value->getKey(), 'type' => class_basename($value)];

                continue;
            }

            if (is_object($value)) {
                $out[$key] = method_exists($value, '__toString')
                    ? $this->truncate((string) $value, $maxLength)
                    : '['.class_basename($value).']';

                continue;
            }

            $out[$key] = is_string($value) ? $this->truncate($value, $maxLength) : $value;
        }

        return $out;
    }

    protected function truncate(?string $value, int $length): ?string
    {
        if ($value === null) {
            return null;
        }

        return strlen($value) > $length ? substr($value, 0, $length).'...' : $value;
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
            'v2.orders.store' => 'Added the order',
            'v2.orders.update' => 'Updated the order',
            'v2.orders.destroy' => 'Deleted the order',
            'v2.orders.bulk-destroy' => 'Bulk deleted orders',
            'portal.orders.store' => 'Added the order',
            'portal.orders.update' => 'Updated the order',
            'driver.order.status' => 'Updated order status',
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
            'admin.users.impersonate' => 'Started impersonating a user',
            'admin.impersonate.stop' => 'Stopped impersonating',
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
