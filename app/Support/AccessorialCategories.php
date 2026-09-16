<?php

namespace App\Support;

/**
 * Origin / destination / end-to-end grouping for accessorials.
 *
 * Seeded names follow the Rose Rocket list the client works from. Names are
 * matched after normalizing case and punctuation, so "Chassi Detention" and
 * "Chassis detention" resolve the same way.
 */
final class AccessorialCategories
{
    public const ORIGIN = 'origin';
    public const DESTINATION = 'destination';
    public const END_TO_END = 'end_to_end';

    public const LABELS = [
        self::ORIGIN => 'Origin',
        self::DESTINATION => 'Destination',
        self::END_TO_END => 'Origin & destination',
    ];

    private const ORIGIN_NAMES = [
        'after hours pickup', 'appointment pickup', 'attempted pickup', 'construction site pickup',
        'container liftup', 'detention time pickup', 'driver assist at origin', 'handbomb at origin',
        'inside pickup', 'limited access pickup', 'lumper at origin', 'notification pickup', 'pmr',
        'port fee gct', 'redirect pickup', 'residential pickup', 'single shipment', 'tailgate pickup',
    ];

    private const DESTINATION_NAMES = [
        'after hours delivery', 'appointment delivery', 'attempted delivery', 'construction site delivery',
        'detention time delivery', 'driver assist at destination', 'handbomb at destination', 'inside delivery',
        'limited access delivery', 'lumper at destination', 'lumper at destnation', 'notification delivery',
        'redelivery charge', 'redirect delivery', 'residential delivery', 'tailgate delivery',
    ];

    public static function forName(?string $name): string
    {
        $key = self::normalize($name);

        if (in_array($key, array_map([self::class, 'normalize'], self::ORIGIN_NAMES), true)) {
            return self::ORIGIN;
        }

        if (in_array($key, array_map([self::class, 'normalize'], self::DESTINATION_NAMES), true)) {
            return self::DESTINATION;
        }

        // Unknown custom accessorials: infer from wording, otherwise applies end to end.
        if (str_contains($key, 'pickup') || str_contains($key, 'origin')) {
            return self::ORIGIN;
        }

        if (str_contains($key, 'delivery') || str_contains($key, 'destination')) {
            return self::DESTINATION;
        }

        return self::END_TO_END;
    }

    public static function label(?string $category): string
    {
        return self::LABELS[$category] ?? self::LABELS[self::END_TO_END];
    }

    private static function normalize(?string $name): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', mb_strtolower((string) $name)));
    }
}
