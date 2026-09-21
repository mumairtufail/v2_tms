<?php

namespace App\Services;

use App\Enums\CustomerNotificationEvent;
use App\Mail\CustomerOrderUpdateMail;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Manifest;
use App\Models\Order;
use App\Models\User;
use App\Notifications\TmsDatabaseNotification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NotificationService
{
    public function listForNotifiable(Authenticatable $notifiable, int $limit = 15): array
    {
        $notifications = $notifiable->notifications()
            ->latest()
            ->limit($limit)
            ->get();

        return [
            'unread_count' => $notifiable->unreadNotifications()->count(),
            'notifications' => $notifications->map(fn ($n) => $this->format($n))->values()->all(),
        ];
    }

    public function markRead(Authenticatable $notifiable, string $id): void
    {
        $notification = $notifiable->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();
    }

    public function markAllRead(Authenticatable $notifiable): void
    {
        $notifiable->unreadNotifications->markAsRead();
    }

    public function notifyUser(User $user, array $payload): void
    {
        if ($user->isInactive()) {
            Log::channel('notifications')->info('User not notified: account inactive', [
                'user_id' => $user->id,
                'type' => $payload['type'] ?? null,
            ]);

            return;
        }

        $user->notify(new TmsDatabaseNotification($payload));
    }

    /**
     * Notify the people at a customer: in-app for everyone with portal access,
     * email for people who opted in to this event on the People tab.
     */
    public function notifyCustomer(Customer $customer, array $payload, ?CustomerNotificationEvent $event = null): void
    {
        $log = Log::channel('notifications');

        if (!$customer->is_active || $customer->is_deleted) {
            $log->info('Customer not notified: account inactive', [
                'customer_id' => $customer->id,
                'type' => $payload['type'] ?? null,
            ]);

            return;
        }

        $customer->loadMissing('company');

        $contacts = $customer->contacts()->get();
        $inApp = 0;
        $emailed = 0;
        $skipped = [];

        foreach ($contacts as $contact) {
            if ($contact->portal_access) {
                $contact->notify(new TmsDatabaseNotification($payload));
                $inApp++;
            }

            if (!$event) {
                continue;
            }

            if ($contact->wantsEmailFor($event)) {
                $sent = app(MailService::class)->queue(new CustomerOrderUpdateMail(
                    recipientName: $contact->first_name,
                    title: $payload['title'] ?? $event->label(),
                    body: $payload['body'] ?? '',
                    url: $contact->portal_access ? ($payload['url'] ?? null) : null,
                    brandName: $customer->company?->name ?? config('app.name'),
                ), $contact->email, $customer->company_id);

                if ($sent) {
                    $emailed++;
                } else {
                    $skipped[$contact->email] = 'email failed to send';
                }
                continue;
            }

            // Why a person did not get an email — the usual support question
            $skipped[$contact->email ?: "contact #{$contact->id}"] = match (true) {
                blank($contact->email) => 'no email address',
                !$event->isAvailable() => 'event not available yet',
                default => 'not opted in to this event',
            };
        }

        $log->info('Customer notified', [
            'customer_id' => $customer->id,
            'company_id' => $customer->company_id,
            'type' => $payload['type'] ?? null,
            'event' => $event?->value,
            'order_id' => $payload['order_id'] ?? null,
            'contacts' => $contacts->count(),
            'in_app' => $inApp,
            'emails_queued' => $emailed,
            'email_skipped' => $skipped,
        ]);
    }

    public function notifyCompanyUsers(Company $company, array $payload, ?string $permission = null, string $action = 'view'): void
    {
        User::query()
            ->where('company_id', $company->id)
            ->active()
            ->get()
            ->filter(fn (User $user) => $permission === null || $user->hasPermission($permission, $action))
            ->each(fn (User $user) => $this->notifyUser($user, $payload));
    }

    public function customerSubmittedOrder(Order $order, Company $company): void
    {
        $order->loadMissing('customer');
        $customerName = $order->customer?->name ?? 'A customer';

        $this->notifyCompanyUsers($company, $this->payload(
            type: 'order_submitted',
            title: 'Customer submitted an order',
            body: "{$customerName} submitted order #{$order->order_number}.",
            icon: 'order',
            url: route('v2.orders.edit', ['company' => $company->slug, 'order' => $order->id]),
            companyId: $company->id,
            orderId: $order->id,
            actor: ['type' => 'customer', 'name' => $customerName],
        ), permission: 'orders');
    }

    /**
     * Not called by the app any more: OrderObserver notifies the customer when the
     * status changes, so every path is covered once. Kept because it is still a valid
     * way to raise this notification by hand — do not wire it into a status change,
     * or the customer will be notified twice.
     */
    public function orderQuotedForCustomer(Order $order, Company $company): void
    {
        $customer = $order->customer;
        if (!$customer) {
            return;
        }

        $this->notifyCustomer($customer, event: CustomerNotificationEvent::Quoted, payload: $this->payload(
            type: 'order_quoted',
            title: 'Your order has been quoted',
            body: "Order #{$order->order_number} is ready for review.",
            icon: 'order',
            url: route('portal.orders.show', ['company' => $company->slug, 'order' => $order->id]),
            companyId: $company->id,
            orderId: $order->id,
        ));
    }

    /**
     * Not called by the app any more — see orderQuotedForCustomer above.
     */
    public function orderBookedForCustomer(Order $order, Company $company): void
    {
        $customer = $order->customer;
        if (!$customer) {
            return;
        }

        $this->notifyCustomer($customer, event: CustomerNotificationEvent::Booked, payload: $this->payload(
            type: 'order_booked',
            title: 'Your order has been booked',
            body: "Order #{$order->order_number} is booked.",
            icon: 'order',
            url: route('portal.orders.show', ['company' => $company->slug, 'order' => $order->id]),
            companyId: $company->id,
            orderId: $order->id,
        ));
    }

    public function orderAssignedToManifest(Order $order, Manifest $manifest, Company $company): void
    {
        $order->loadMissing('customer');
        $manifestCode = $manifest->code;

        if ($order->customer) {
            $this->notifyCustomer($order->customer, $this->payload(
                type: 'order_manifest_assigned',
                title: 'Order assigned to a manifest',
                body: "Order #{$order->order_number} was assigned to manifest {$manifestCode}.",
                icon: 'manifest',
                url: route('portal.orders.show', ['company' => $company->slug, 'order' => $order->id]),
                companyId: $company->id,
                orderId: $order->id,
                manifestId: $manifest->id,
            ));
        }

        $manifest->loadMissing('drivers');
        foreach ($manifest->drivers as $driver) {
            $this->notifyUser($driver, $this->payload(
                type: 'driver_order_assigned',
                title: 'New order on your manifest',
                body: "Order #{$order->order_number} was added to manifest {$manifestCode}.",
                icon: 'order',
                url: null,
                companyId: $company->id,
                orderId: $order->id,
                manifestId: $manifest->id,
            ));
        }
    }

    public function manifestCreated(Manifest $manifest, Company $company, ?User $actor = null): void
    {
        $this->notifyCompanyUsers($company, $this->payload(
            type: 'manifest_created',
            title: 'Manifest created',
            body: "Manifest {$manifest->code} was created" . ($actor ? " by {$actor->name}" : '') . '.',
            icon: 'manifest',
            url: route('v2.manifests.edit', ['company' => $company->slug, 'manifest' => $manifest->id]),
            companyId: $company->id,
            manifestId: $manifest->id,
            actor: $actor ? ['type' => 'user', 'name' => $actor->name] : null,
        ), permission: 'manifests');
    }

    public function driverAssignedToManifest(Manifest $manifest, User $driver, Company $company, ?User $actor = null): void
    {
        $this->notifyUser($driver, $this->payload(
            type: 'driver_manifest_assigned',
            title: 'You were assigned to a manifest',
            body: "You were assigned to manifest {$manifest->code}.",
            icon: 'manifest',
            url: null,
            companyId: $company->id,
            manifestId: $manifest->id,
            actor: $actor ? ['type' => 'user', 'name' => $actor->name] : null,
        ));

        $this->notifyCompanyUsers($company, $this->payload(
            type: 'manifest_driver_assigned',
            title: 'Driver assigned to manifest',
            body: "{$driver->name} was assigned to manifest {$manifest->code}.",
            icon: 'manifest',
            url: route('v2.manifests.edit', ['company' => $company->slug, 'manifest' => $manifest->id]),
            companyId: $company->id,
            manifestId: $manifest->id,
            actor: $actor ? ['type' => 'user', 'name' => $actor->name] : null,
        ), permission: 'manifests');
    }

    public function driverUpdatedOrderStatus(Order $order, User $driver, string $fromStatus, string $toStatus, ?string $note = null): void
    {
        $order->loadMissing(['customer', 'company']);
        $company = $order->company;
        if (!$company) {
            return;
        }

        $label = Str::headline(str_replace('_', ' ', $toStatus));
        $body = "{$driver->name} updated order #{$order->order_number} to {$label}.";
        if ($note) {
            $body .= ' Note: ' . Str::limit($note, 120);
        }

        $this->notifyCompanyUsers($company, $this->payload(
            type: 'driver_order_status',
            title: 'Driver updated order status',
            body: $body,
            icon: 'driver',
            url: route('v2.orders.edit', ['company' => $company->slug, 'order' => $order->id]),
            companyId: $company->id,
            orderId: $order->id,
            meta: ['from_status' => $fromStatus, 'to_status' => $toStatus],
            actor: ['type' => 'driver', 'name' => $driver->name],
        ), permission: 'orders');

        // The customer is notified by OrderObserver when the status itself changes,
        // so this only tells the office side.
    }

    public function driverUpdatedManifestStatus(Manifest $manifest, User $driver, string $toStatus, Company $company): void
    {
        $label = Str::headline(str_replace('_', ' ', $toStatus));

        $this->notifyCompanyUsers($company, $this->payload(
            type: 'driver_manifest_status',
            title: 'Driver updated manifest status',
            body: "{$driver->name} marked manifest {$manifest->code} as {$label}.",
            icon: 'driver',
            url: route('v2.manifests.edit', ['company' => $company->slug, 'manifest' => $manifest->id]),
            companyId: $company->id,
            manifestId: $manifest->id,
            meta: ['to_status' => $toStatus],
            actor: ['type' => 'driver', 'name' => $driver->name],
        ), permission: 'manifests');

        $manifest->loadMissing(['directOrders.customer', 'orders.customer']);
        $orders = $manifest->directOrders->merge($manifest->orders)->unique('id');

        foreach ($orders as $order) {
            if (!$order->customer) {
                continue;
            }

            $this->notifyCustomer($order->customer, $this->payload(
                type: 'manifest_status_updated',
                title: 'Shipment update',
                body: "Manifest {$manifest->code} for order #{$order->order_number} is now {$label}.",
                icon: 'manifest',
                url: route('portal.orders.show', ['company' => $company->slug, 'order' => $order->id]),
                companyId: $company->id,
                orderId: $order->id,
                manifestId: $manifest->id,
                meta: ['to_status' => $toStatus],
                actor: ['type' => 'driver', 'name' => $driver->name],
            ));
        }
    }

    protected function payload(
        string $type,
        string $title,
        string $body,
        string $icon,
        ?string $url,
        ?int $companyId = null,
        ?int $orderId = null,
        ?int $manifestId = null,
        ?array $meta = null,
        ?array $actor = null,
    ): array {
        return array_filter([
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'icon' => $icon,
            'url' => $url,
            'company_id' => $companyId,
            'order_id' => $orderId,
            'manifest_id' => $manifestId,
            'meta' => $meta,
            'actor' => $actor,
        ], fn ($value) => $value !== null);
    }

    protected function format(DatabaseNotification $notification): array
    {
        $data = $notification->data;

        return [
            'id' => $notification->id,
            'type' => $data['type'] ?? 'info',
            'title' => $data['title'] ?? 'Notification',
            'body' => $data['body'] ?? '',
            'icon' => $data['icon'] ?? 'info',
            'url' => $data['url'] ?? null,
            'read_at' => optional($notification->read_at)?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
            'created_at_human' => $notification->created_at?->diffForHumans(),
        ];
    }
}
