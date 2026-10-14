<?php

namespace App\Services;

use App\Models\ActivityLogs;
use App\Models\Company;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ActivityLogListingService
{
    /**
     * @param  bool  $privileged  A super admin is looking. Without it the
     *                            listing is restricted to what a company is
     *                            shown about its own account.
     */
    public function paginate(Request $request, ?int $companyId = null, bool $privileged = true): LengthAwarePaginator
    {
        $query = ActivityLogs::with(['user', 'impersonator', 'customer', 'company'])
            ->when($companyId, fn ($q) => $q->forCompany($companyId))
            ->unless($privileged, fn ($q) => $q->visibleToCompany());

        if ($request->filled('search')) {
            $searchTerm = $request->search;

            // Searches the row's own denormalised columns rather than four
            // correlated subqueries against users, customers and companies.
            // It is far cheaper on a large table, and it still finds entries
            // whose user account has since been deleted.
            $query->where(function ($q) use ($searchTerm) {
                $q->where('actor_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('actor_email', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('impersonator_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('action', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('data->description', 'LIKE', "%{$searchTerm}%")
                    // Anchored: an IP search is a prefix, so this one can use
                    // the index instead of scanning.
                    ->orWhere('ip_address', 'LIKE', "{$searchTerm}%");
            });
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('status')) {
            $query->where('is_successful', $request->status === 'success');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }

        // input() rather than property access for these three: 'session', 'ip'
        // and 'user' are all real methods on Request, and reading them as
        // properties is a trap waiting for the next person.
        if ($request->filled('session')) {
            $query->where('session_id', $request->input('session'));
        }

        if ($request->filled('ip')) {
            $query->where('ip_address', $request->input('ip'));
        }

        if ($request->filled('user')) {
            $query->where('user_id', $request->input('user'));
        }

        // Every entry produced by one HTTP request: the request row, the model
        // changes it caused and any error it raised.
        if ($request->filled('request')) {
            $query->where('request_id', $request->input('request'));
        }

        if ($privileged && $request->boolean('impersonated')) {
            $query->whereNotNull('impersonator_id');
        }

        if ($request->boolean('noteworthy')) {
            $query->noteworthy();
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        return $query->orderByDesc('created_at')->paginate(20)->withQueryString();
    }

    public function forOrder(Company $company, Order $order, int $limit = 50): Collection
    {
        return ActivityLogs::with(['user', 'impersonator', 'customer'])
            ->forCompany($company->id)
            ->forOrder($order->id)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function (ActivityLogs $log) {
                return [
                    'id' => $log->id,
                    'actor' => $log->actor_name,
                    'impersonator' => $log->impersonator_name,
                    'description' => $log->description,
                    'successful' => (bool) $log->is_successful,
                    'severity' => $log->severity,
                    'created_at' => $log->created_at?->toIso8601String(),
                    'created_at_human' => $log->created_at?->diffForHumans(),
                    'created_at_label' => $log->created_at?->format('M j, Y g:i A'),
                ];
            })
            ->values();
    }

    /**
     * The full record behind one row. Everything captured at write time is
     * returned, so the stored context is actually reviewable rather than just
     * sitting in the data column.
     */
    public function detail(ActivityLogs $log, bool $includeTechnical = false): array
    {
        $log->loadMissing(['user', 'impersonator', 'customer', 'company', 'order']);

        $base = $this->baseDetail($log);

        if (! $includeTechnical) {
            // A company user is not told that support was involved, so the
            // impersonator never reaches the response - not even as a flag.
            unset($base['impersonator'], $base['impersonated']);

            return $base;
        }

        // Super admin only. Stack traces, SQL and headers name internal paths,
        // table structure and other tenants' shapes; they are a diagnostic
        // tool, not something to show a customer.
        // array_merge, not +: the union operator keeps the left-hand value, so
        // the description override below would be silently discarded.
        return array_merge($base, [
            // Re-attaches the support context the company view never receives.
            'description' => $log->description_with_actor,
            'technical' => $log->technical ?: null,
            'request_id' => $log->request_id,
            'raw_data' => $log->data,
        ]);
    }

    protected function baseDetail(ActivityLogs $log): array
    {
        return [
            'id' => $log->id,
            'description' => $log->description,
            'action' => $log->action,
            'category' => $log->category,
            'category_label' => $log->category_label,
            'severity' => $log->severity,
            'successful' => (bool) $log->is_successful,
            'actor' => $log->actor_name,
            'actor_email' => $log->actor_email,
            'impersonator' => $log->impersonator_name,
            'impersonated' => (bool) $log->is_impersonated,
            'company' => $log->company?->name,
            'order_id' => $log->order_id,
            'subject' => $log->subject_type ? class_basename($log->subject_type).' #'.$log->subject_id : null,
            'method' => $log->method,
            'url' => $log->url,
            'status_code' => $log->status_code,
            'duration_ms' => $log->duration_ms,
            'ip_address' => $log->ip_address,
            'user_agent' => $log->user_agent,
            'session_id' => $log->session_id,
            // created_at stays a formatted UTC string as a fallback; created_at_iso
            // carries the instant so the drawer can show it in the viewer's own
            // timezone (see resources/views/partials/local-time.blade.php).
            'created_at' => $log->created_at?->format('M j, Y g:i:s A'),
            'created_at_iso' => $log->created_at?->toIso8601String(),
            'created_at_human' => $log->created_at?->diffForHumans(),
            'changes' => $log->data['changes'] ?? null,
            // Route internals and raw input stay out of the shared view; a
            // company user wants what happened, not how it was routed.
            'context' => collect($log->data ?? [])
                ->except([
                    'description', 'changes', 'actor_name', 'actor_type',
                    'actor_email', 'category', 'route', 'route_parameters',
                    'model_type', 'model_id', 'is_successful', 'severity',
                    'status_code', 'duration_ms',
                    // Support access is surfaced through the dedicated fields
                    // for a super admin, and not at all for a company user -
                    // leaving these here put the staff name back in the payload.
                    'impersonated', 'impersonator_name', 'impersonator_id',
                ])
                ->filter(fn ($value) => $value !== null && $value !== [] && $value !== '')
                ->all(),
        ];
    }

    /**
     * Counts for the summary strip above the table, respecting the filters
     * already applied so the numbers describe what is on screen.
     */
    public function summary(Request $request, ?int $companyId = null, bool $privileged = true): array
    {
        $base = fn () => ActivityLogs::query()->when($companyId, fn ($q) => $q->forCompany($companyId))
            ->unless($privileged, fn ($q) => $q->visibleToCompany())
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to));

        return [
            'total' => $base()->count(),
            'failed' => $base()->where('is_successful', false)->count(),
            'denied' => $base()->where('category', 'authorization')->count(),
            'errors' => $base()->whereIn('severity', ['error', 'critical'])->count(),
            // Only meaningful to a super admin; the company view is not shown
            // that support access happened at all.
            'impersonated' => $privileged ? $base()->whereNotNull('impersonator_id')->count() : null,
        ];
    }
}
