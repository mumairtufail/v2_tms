{{--
    A stored UTC timestamp shown on the system clock (Mountain Time, see
    App\Support\DisplayTime), with the zone abbreviation on formats that show
    a clock.

        <x-local-time :value="$log->created_at" />
        <x-local-time :value="$log->created_at" format="short" class="text-xs" />
        <x-local-time :value="$setting->last_tested_at" format="date" fallback="Never" />

    Rendered on the server, so it is already correct without scripts. The
    partial resources/views/partials/local-time.blade.php adds the UTC tooltip
    and formats times that Alpine renders later (log drawer, order activity).

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
    $localTimeDate = \App\Support\DisplayTime::in($value);

    $localTimeFormat = match ($format) {
        'datetime-seconds' => 'M j, Y g:i:s A',
        'short' => 'M j, g:i A',
        'date' => 'M j, Y',
        'time' => 'g:i A',
        default => 'M j, Y g:i A',
    };
@endphp

@if($localTimeDate)
    <time datetime="{{ $localTimeDate->copy()->utc()->toIso8601String() }}"
          title="{{ $localTimeDate->format('l, F j, Y g:i:s A T') }}&#10;{{ $localTimeDate->copy()->utc()->format('M j, Y g:i:s A') }} UTC"
          {{ $attributes }}>{{ \App\Support\DisplayTime::format($localTimeDate, $localTimeFormat) }}</time>
@else
    <span {{ $attributes }}>{{ $fallback }}</span>
@endif
