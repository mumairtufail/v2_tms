<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Draft = 'draft';
    case New = 'new';
    case Quoted = 'quoted';
    case NoQuote = 'no_quote';
    case Booked = 'booked';
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
            self::Booked => 'Booked',
            self::Warehousing => 'Warehousing',
            self::PickedUp => 'Picked Up',
            self::InTransit => 'In Transit',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Compact badge code for dense list views (e.g. BKD for Booked).
     */
    public function shortCode(): string
    {
        return match ($this) {
            self::Draft => 'DRF',
            self::New => 'NEW',
            self::Quoted => 'QTD',
            self::NoQuote => 'NQT',
            self::Booked => 'BKD',
            self::Warehousing => 'WHS',
            self::PickedUp => 'PKU',
            self::InTransit => 'TRN',
            self::Delivered => 'DLV',
            self::Cancelled => 'CAN',
        };
    }

    /**
     * Office-side steps before booking: Draft -> New -> Quoted / No Quote.
     * Only the web app moves an order through these; the driver app takes over once it is Booked.
     *
     * @return self[]
     */
    public static function preBooking(): array
    {
        return [self::Draft, self::New, self::Quoted, self::NoQuote];
    }

    public function isPreBooking(): bool
    {
        return in_array($this, self::preBooking(), true);
    }

    /**
     * True once the order has left the office-side steps (booked, a driver status, or cancelled).
     * Unrecognised legacy values count as not past booking.
     */
    public static function isPastBooking(?string $status): bool
    {
        $case = self::tryFrom((string) $status);

        return $case !== null && !$case->isPreBooking();
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
        // Booked is the hand-off to the driver, so its next step is the start of the driver flow.
        if ($this === self::Booked) {
            return self::Warehousing;
        }

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
