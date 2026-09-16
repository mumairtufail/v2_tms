<?php

namespace App\Observers;

use App\Enums\CustomerNotificationEvent;
use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Customer notifications for order status changes.
 *
 * Status is changed from several places — the order form, book/unbook, the driver app —
 * so this watches the model itself rather than each of those. Every path therefore
 * notifies the customer once, and a new path cannot forget to.
 *
 * Each person at the customer gets the in-app notification if they have portal access,
 * and an email only if they opted in to that particular status on the People tab.
 */
class OrderObserver
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $from = (string) $order->getOriginal('status');
        $to = (string) $order->status;

        $event = CustomerNotificationEvent::forOrderStatus($to);

        // Statuses with no customer-facing event (draft, new, no_quote, warehousing…)
        // stay internal: nothing is sent and nobody is left wondering why.
        if (! $event) {
            Log::channel('notifications')->info('Order status changed with no customer event', [
                'order_id' => $order->id,
                'from' => $from,
                'to' => $to,
            ]);

            return;
        }

        $order->loadMissing(['customer', 'company']);

        if (! $order->customer || ! $order->company) {
            return;
        }

        $label = Str::headline(str_replace('_', ' ', $to));

        app(NotificationService::class)->notifyCustomer(
            $order->customer,
            event: $event,
            payload: [
                'type' => 'order_' . $to,
                'title' => $this->title($to, $label),
                'body' => "Order #{$order->order_number} is now {$label}.",
                'icon' => 'order',
                'url' => route('portal.orders.show', [
                    'company' => $order->company->slug,
                    'order' => $order->id,
                ]),
                'company_id' => $order->company_id,
                'order_id' => $order->id,
                'meta' => ['from_status' => $from, 'to_status' => $to],
            ],
        );
    }

    private function title(string $status, string $label): string
    {
        return match ($status) {
            'quoted' => 'Your order has been quoted',
            'booked' => 'Your order has been booked',
            'in_transit' => 'Your order is on its way',
            'delivered' => 'Your order has been delivered',
            default => "Order {$label}",
        };
    }
}
