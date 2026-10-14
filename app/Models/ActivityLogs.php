<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLogs extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'auth' => 'Authentication',
        'authorization' => 'Permission denied',
        'impersonation' => 'Impersonation',
        'http' => 'Changes',
        'read' => 'Views',
        'model' => 'Record changes',
        'profile' => 'Profile',
        'error' => 'Errors',
        'security' => 'Security',
        'general' => 'Other',
    ];

    public const SEVERITIES = [
        'info' => 'Info',
        'notice' => 'Notice',
        'warning' => 'Warning',
        'error' => 'Error',
        'critical' => 'Critical',
    ];

    /**
     * Categories a company user is offered.
     *
     * Platform-level noise is left out: impersonation and raw errors describe
     * what the operator did or what broke internally, not the company's work.
     */
    public static function companyCategories(): array
    {
        return collect(self::CATEGORIES)
            ->except(['impersonation', 'error', 'security', 'general'])
            ->all();
    }

    protected $fillable = [
        'user_id',
        'impersonator_id',
        'actor_name',
        'actor_email',
        'impersonator_name',
        'customer_id',
        'company_id',
        'order_id',
        'session_id',
        'request_id',
        'subject_type',
        'subject_id',
        'action',
        'category',
        'severity',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'status_code',
        'duration_ms',
        'is_successful',
        'data',
        'technical',
    ];

    protected $casts = [
        'data' => 'array',
        'technical' => 'array',
        'is_successful' => 'boolean',
        'status_code' => 'integer',
        'duration_ms' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function impersonator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'impersonator_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * What happened, with no mention of support access.
     *
     * Entries written before impersonation moved into its own columns have the
     * "(as X, impersonated by Y)" wording baked into the stored text, so it is
     * stripped here rather than left to leak into the company's view.
     */
    protected function description(): Attribute
    {
        return Attribute::get(function () {
            $description = $this->data['description'] ?? $this->action;

            return trim(preg_replace('/\s*\(as .+?, impersonated by .+?\)\s*$/u', '', $description));
        });
    }

    /**
     * The same line with the support context restored, for the super admin
     * view only.
     */
    protected function descriptionWithActor(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->is_impersonated) {
                return $this->description;
            }

            return sprintf(
                '%s (as %s, impersonated by %s)',
                $this->description,
                $this->actor_name,
                $this->impersonator_name ?? 'a super admin'
            );
        });
    }

    /**
     * Who acted.
     *
     * The stored column wins over the relation: it costs no query, and it is
     * still correct after the user record is deleted - which is exactly when
     * an audit trail is asked who did something.
     */
    protected function actorName(): Attribute
    {
        return Attribute::get(function (?string $stored) {
            if (! empty($stored)) {
                return $this->customer_id ? $stored.' (Customer)' : $stored;
            }

            if ($this->relationLoaded('user') && $this->user) {
                return $this->user->name;
            }

            if ($this->relationLoaded('customer') && $this->customer) {
                return $this->customer->name.' (Customer)';
            }

            return $this->data['actor_name'] ?? 'System';
        });
    }

    protected function actorEmail(): Attribute
    {
        return Attribute::get(fn (?string $stored) => $stored ?: ($this->data['actor_email'] ?? null));
    }

    /**
     * The real human behind the action when it was performed through an
     * impersonated session.
     */
    protected function impersonatorName(): Attribute
    {
        return Attribute::get(function (?string $stored) {
            if (! empty($stored)) {
                return $stored;
            }

            if ($this->relationLoaded('impersonator') && $this->impersonator) {
                return $this->impersonator->name;
            }

            return $this->data['impersonator_name'] ?? null;
        });
    }

    /**
     * True only when one person was acting as another.
     *
     * The impersonation start and stop entries record the super admin in both
     * columns, which is not somebody acting as somebody else - without this the
     * super admin's own row reads "acting: themselves".
     */
    protected function isImpersonated(): Attribute
    {
        return Attribute::get(function () {
            if ($this->impersonator_id !== null) {
                return (int) $this->impersonator_id !== (int) $this->user_id;
            }

            return (bool) ($this->data['impersonated'] ?? false);
        });
    }

    /**
     * Entries a company is shown about its own account.
     *
     * Support access is platform operation, not the company's activity, so the
     * impersonation start and stop entries are left out entirely. The rows stay
     * in the table and remain visible in the super admin view.
     */
    public function scopeVisibleToCompany($query)
    {
        // Null-safe on purpose. `category != 'impersonation'` evaluates to NULL
        // for a row whose category is NULL, which SQL treats as not-true, so a
        // plain comparison would hide every entry written before these columns
        // existed rather than just the impersonation ones.
        return $query
            ->where(function ($q) {
                $q->whereNull('category')->orWhere('category', '!=', 'impersonation');
            })
            ->where(function ($q) {
                $q->whereNull('severity')->orWhereNotIn('severity', ['error', 'critical']);
            })
            // Entries the middleware wrote for the impersonate routes before
            // those routes were excluded. Filtered rather than deleted, so the
            // super admin view keeps them.
            ->whereNotIn('action', ['admin.users.impersonate', 'admin.impersonate.stop']);
    }

    protected function categoryLabel(): Attribute
    {
        return Attribute::get(fn () => self::CATEGORIES[$this->category] ?? ucfirst((string) $this->category));
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query;
    }

    /**
     * Entries about one order.
     *
     * Indexed columns only. The previous version OR'd in three leading-wildcard
     * LIKEs against url, which forced a full table scan on every order page and
     * got slower as the table grew. Rows written before order_id existed are
     * reachable through the backfill migration instead.
     */
    public function scopeForOrder($query, int $orderId)
    {
        return $query->where(function ($q) use ($orderId) {
            $q->where('order_id', $orderId)
                ->orWhere(function ($sub) use ($orderId) {
                    $sub->where('subject_type', Order::class)
                        ->where('subject_id', $orderId);
                });
        });
    }

    /**
     * Everything that happened in one browser session, oldest first - the view
     * a reviewer wants when reconstructing what somebody did in a sitting.
     */
    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId)->orderBy('created_at');
    }

    public function scopeForSubject($query, string $type, int $id)
    {
        return $query->where('subject_type', $type)->where('subject_id', $id);
    }

    /**
     * Rows a reviewer should look at first: failures, denials, errors and
     * anything done while impersonating.
     */
    public function scopeNoteworthy($query)
    {
        return $query->where(function ($q) {
            $q->whereIn('severity', ['warning', 'error', 'critical'])
                ->orWhere('is_successful', false)
                ->orWhereNotNull('impersonator_id');
        });
    }
}
