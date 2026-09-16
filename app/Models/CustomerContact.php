<?php

namespace App\Models;

use App\Enums\CustomerNotificationEvent;
use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * A person at a customer (People tab). People with portal access sign in
 * to the customer portal through the "customer" guard.
 */
class CustomerContact extends Authenticatable
{
    use BelongsToCompany, Notifiable, SoftDeletes;

    protected $fillable = [
        'company_id',
        'customer_id',
        'first_name',
        'last_name',
        'job_title',
        'email',
        'notes',
        'avatar_path',
        'send_invoices',
        'send_reports',
        'send_dispatch_notifications',
        'portal_access',
        'cc_on_invoices',
        'notification_prefs',
        'password',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'send_invoices' => 'boolean',
        'send_reports' => 'boolean',
        'send_dispatch_notifications' => 'boolean',
        'portal_access' => 'boolean',
        'cc_on_invoices' => 'boolean',
        'notification_prefs' => 'array',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function phones(): HasMany
    {
        return $this->hasMany(CustomerContactPhone::class);
    }

    protected function name(): Attribute
    {
        return Attribute::get(fn () => trim($this->first_name . ' ' . $this->last_name));
    }

    public function initials(): string
    {
        return mb_strtoupper(mb_substr((string) $this->first_name, 0, 1) . mb_substr((string) $this->last_name, 0, 1)) ?: '?';
    }

    public function canUsePortal(): bool
    {
        $customer = $this->customer;

        return $this->portal_access
            && filled($this->password)
            && $customer
            && $customer->is_active
            && !$customer->is_deleted;
    }

    /** @return array<string, array{in_app: bool, email: bool}> */
    public function preferences(): array
    {
        return array_replace_recursive(CustomerNotificationEvent::defaults(), $this->notification_prefs ?? []);
    }

    public function wantsEmailFor(CustomerNotificationEvent $event): bool
    {
        return $event->isAvailable()
            && filled($this->email)
            && (bool) ($this->preferences()[$event->value]['email'] ?? false);
    }
}
