<?php

namespace App\Enums;

/**
 * Order events a customer's people can subscribe to (People tab grid).
 * In-app is always on for people with portal access; email is opt-in per event.
 */
enum CustomerNotificationEvent: string
{
    case Quoted = 'order_quoted';
    case SpotQuoted = 'order_spot_quoted';
    case Booked = 'order_booked';
    case InTransit = 'order_in_transit';
    case Delivered = 'order_delivered';
    case Invoiced = 'order_invoiced';
    case Paid = 'order_paid';
    case Claim = 'order_claim';
    case PodUploaded = 'order_pod_uploaded';
    case Chat = 'order_chat';

    public function label(): string
    {
        return match ($this) {
            self::Quoted => 'Order Quoted',
            self::SpotQuoted => 'Order Spot Quoted',
            self::Booked => 'Order Booked',
            self::InTransit => 'Order In Transit',
            self::Delivered => 'Order Delivered',
            self::Invoiced => 'Order Invoiced',
            self::Paid => 'Order Paid',
            self::Claim => 'Order Claim',
            self::PodUploaded => 'Order POD Uploaded',
            self::Chat => 'Order Chat',
        };
    }

    /**
     * Whether something in the app actually sends this event today.
     * The rest are shown as "coming soon" so nobody expects emails that never arrive.
     */
    public function isAvailable(): bool
    {
        return in_array($this, [self::Quoted, self::Booked, self::InTransit, self::Delivered], true);
    }

    public static function forOrderStatus(string $status): ?self
    {
        return match ($status) {
            'quoted' => self::Quoted,
            'booked' => self::Booked,
            'in_transit' => self::InTransit,
            'delivered' => self::Delivered,
            default => null,
        };
    }

    /** @return array<string, array{in_app: bool, email: bool}> */
    public static function defaults(): array
    {
        $prefs = [];
        foreach (self::cases() as $event) {
            $prefs[$event->value] = ['in_app' => true, 'email' => false];
        }

        return $prefs;
    }

    /**
     * Build stored preferences from submitted email checkboxes.
     * Only events that can fire keep an email opt-in.
     *
     * @param  array<string, mixed>  $emailInput  event value => truthy
     */
    public static function prefsFromEmailInput(array $emailInput): array
    {
        $prefs = self::defaults();
        foreach (self::cases() as $event) {
            $prefs[$event->value]['email'] = $event->isAvailable() && filter_var($emailInput[$event->value] ?? false, FILTER_VALIDATE_BOOLEAN);
        }

        return $prefs;
    }
}
