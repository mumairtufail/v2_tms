{{--
    A UTC timestamp rendered in the viewer's own timezone.

        <x-local-time :value="$log->created_at" />
        <x-local-time :value="$log->created_at" format="short" class="text-xs" />
        <x-local-time :value="$setting->last_tested_at" format="date" fallback="Never" />

    The text rendered here is the UTC fallback; resources/views/partials/local-time.blade.php
    rewrites it in the browser. That partial must be present on the page — it is
    included by the v2 and portal layouts.

    Only use this for a genuine UTC instant (a Carbon-cast model column). Order
    stop times are stored as pre-formatted local wall-clock strings, so shifting
    them would move real pickup and delivery windows.

    Formats: datetime (default), datetime-seconds, short, date, time.
--}}
@props([
    'value' => null,
    'format' => 'datetime',
    'fallback' => '—',
])

@php
    try {
        $localTimeDate = $value instanceof \DateTimeInterface
            ? \Illuminate\Support\Carbon::instance($value)
            : (filled($value) ? \Illuminate\Support\Carbon::parse($value) : null);
    } catch (\Throwable) {
        // An unparseable value must not take the page down.
        $localTimeDate = null;
    }

    $localTimeFallbackFormat = match ($format) {
        'datetime-seconds' => 'M j, Y g:i:s A',
        'short' => 'M j, g:i A',
        'date' => 'M j, Y',
        'time' => 'g:i A',
        default => 'M j, Y g:i A',
    };

    // Only worth saying on a format that shows a clock. On a date-only fallback
    // "UTC" is just noise, and this text is only ever seen before the script runs.
    $localTimeFallbackSuffix = $format === 'date' ? '' : ' UTC';
@endphp

@if($localTimeDate)
    <time datetime="{{ $localTimeDate->toIso8601String() }}"
          data-local-time="{{ $format }}"
          {{ $attributes }}>{{ $localTimeDate->format($localTimeFallbackFormat) }}{{ $localTimeFallbackSuffix }}</time>
@else
    <span {{ $attributes }}>{{ $fallback }}</span>
@endif
