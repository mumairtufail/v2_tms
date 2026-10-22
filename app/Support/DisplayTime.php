<?php

namespace App\Support;

use Carbon\CarbonInterface;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * The one clock every screen, email, PDF and report shows times in.
 *
 * Storage stays in UTC (config/app.php 'timezone'); only what people see is
 * converted, to config('app.display_timezone'), which is Mountain Time. Before
 * this, some places printed raw UTC with no label and others converted to the
 * viewer's own browser zone, so the same record could show two different times.
 *
 * Do not use this for order stop windows (pickup / delivery). Those are stored
 * as the local wall-clock time at the shipper or consignee, and converting them
 * would move real appointments.
 */
class DisplayTime
{
    public const DEFAULT_TIMEZONE = 'America/Edmonton';

    private static ?string $resolved = null;

    /** IANA name, e.g. "America/Edmonton". Falls back to the default if misconfigured. */
    public static function tz(): string
    {
        if (self::$resolved !== null) {
            return self::$resolved;
        }

        $configured = (string) config('app.display_timezone', self::DEFAULT_TIMEZONE);

        if (! in_array($configured, DateTimeZone::listIdentifiers(), true)) {
            Log::warning('Invalid APP_DISPLAY_TIMEZONE, falling back to default', [
                'configured' => $configured,
                'fallback' => self::DEFAULT_TIMEZONE,
            ]);
            $configured = self::DEFAULT_TIMEZONE;
        }

        return self::$resolved = $configured;
    }

    /** Human name of the zone for headings, e.g. "Mountain Time". */
    public static function label(): string
    {
        return (string) config('app.display_timezone_label', 'Mountain Time');
    }

    /** Current abbreviation, MST or MDT depending on the date. */
    public static function abbreviation(?DateTimeInterface $at = null): string
    {
        return self::in($at ?? now())?->format('T') ?? '';
    }

    /** A copy of the value in the display zone, or null for empty/unparseable input. */
    public static function in(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            $date = $value instanceof DateTimeInterface
                ? Carbon::instance($value)
                : Carbon::parse($value, 'UTC');
        } catch (\Throwable) {
            return null;
        }

        return $date->copy()->setTimezone(self::tz());
    }

    /**
     * Format a stored instant for display. Formats that show a clock get the
     * zone abbreviation appended ("Oct 23, 2026 2:15 PM MDT") so nobody has to
     * guess which clock it is; date-only formats do not.
     */
    public static function format(mixed $value, string $format = 'M j, Y g:i A', ?bool $withZone = null, string $fallback = ''): string
    {
        $date = self::in($value);

        if (! $date) {
            return $fallback;
        }

        $withZone ??= (bool) preg_match('/[gGhHisaA]/', $format);

        return $date->format($format).($withZone ? ' '.$date->format('T') : '');
    }

    public static function now(): Carbon
    {
        return now(self::tz());
    }

    /**
     * Start and end of a calendar day / week / month on the display clock,
     * returned in UTC so they can go straight into a query.
     *
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    public static function range(string $period, ?CarbonInterface $at = null): array
    {
        $local = ($at ? Carbon::instance($at) : Carbon::now())->setTimezone(self::tz());

        [$start, $end] = match ($period) {
            'week' => [$local->copy()->startOfWeek(), $local->copy()->endOfWeek()],
            'month' => [$local->copy()->startOfMonth(), $local->copy()->endOfMonth()],
            default => [$local->copy()->startOfDay(), $local->copy()->endOfDay()],
        };

        return [$start->utc(), $end->utc()];
    }

    /** Midnight-to-midnight of a Y-m-d date on the display clock, in UTC. Null if invalid. */
    public static function dayBounds(mixed $ymd): ?array
    {
        if (! is_string($ymd) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymd)) {
            return null;
        }

        try {
            $day = Carbon::createFromFormat('!Y-m-d', $ymd, self::tz());
        } catch (\Throwable) {
            return null;
        }

        return [$day->copy()->startOfDay()->utc(), $day->copy()->endOfDay()->utc()];
    }
}
