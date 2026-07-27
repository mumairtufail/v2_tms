<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Draft = 'draft';
    case New = 'new';
    case Quoted = 'quoted';
    case NoQuote = 'no_quote';
    case Warehousing = 'warehousing';
    case PickedUp = 'picked_up';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::New => 'New',
            self::Quoted => 'Quoted',
            self::NoQuote => 'No Quote',
            self::Warehousing => 'Warehousing',
            self::PickedUp => 'Picked Up',
            self::InTransit => 'In Transit',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * The driver-app status stepper: Warehousing -> Picked Up -> In Transit -> Delivered.
     * Cancelled is a separate terminal branch, not part of this ordered flow.
     *
     * @return self[]
     */
    public static function driverWorkflow(): array
    {
        return [self::Warehousing, self::PickedUp, self::InTransit, self::Delivered];
    }

    public function nextDriverStatus(): ?self
    {
        $flow = self::driverWorkflow();
        $index = array_search($this, $flow, true);

        if ($index === false || $index === count($flow) - 1) {
            return null;
        }

        return $flow[$index + 1];
    }

    public function canTransitionTo(self $target): bool
    {
        if ($target === self::Cancelled) {
            return $this !== self::Delivered && $this !== self::Cancelled;
        }

        return $this->nextDriverStatus() === $target;
    }
}
