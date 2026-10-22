<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Timestamps are stored in UTC and shown everywhere on one clock, Mountain
 * Time (config app.display_timezone), with the zone named, so a record shows
 * the same time on every screen, email and PDF.
 */
class LocalTimeRenderingTest extends TestCase
{
    use RefreshDatabase;

    private function render(array $data): string
    {
        return view('components.local-time', array_merge([
            'format' => 'datetime',
            'fallback' => '—',
            'attributes' => new \Illuminate\View\ComponentAttributeBag([]),
        ], $data))->render();
    }

    public function test_it_emits_the_instant_as_iso_8601_with_an_offset(): void
    {
        // Without the offset the browser would read it as local time and the
        // whole conversion would silently become a no-op.
        $html = $this->render(['value' => Carbon::parse('2026-10-13 14:30:00')]);

        $this->assertStringContainsString('datetime="2026-10-13T14:30:00+00:00"', $html);
    }

    public function test_times_are_shown_in_mountain_time_with_the_zone(): void
    {
        // 14:30 UTC in October is 8:30 AM Mountain Daylight Time
        $html = $this->render(['value' => Carbon::parse('2026-10-13 14:30:00')]);

        $this->assertStringContainsString('Oct 13, 2026 8:30 AM MDT', $html);
    }

    public function test_winter_times_use_standard_time(): void
    {
        // 14:30 UTC in January is 7:30 AM Mountain Standard Time
        $html = $this->render(['value' => Carbon::parse('2026-01-13 14:30:00')]);

        $this->assertStringContainsString('Jan 13, 2026 7:30 AM MST', $html);
    }

    public function test_a_date_only_value_uses_the_mountain_calendar_day(): void
    {
        // 03:00 UTC on Oct 13 is still the evening of Oct 12 in Mountain Time
        $html = $this->render(['value' => Carbon::parse('2026-10-13 03:00:00'), 'format' => 'date']);

        // Visible text is the date alone; the zone only appears in the tooltip
        $this->assertMatchesRegularExpression('/>\s*Oct 12, 2026\s*<\/time>/', $html);
    }

    public function test_hovering_shows_the_utc_time_as_well(): void
    {
        $html = $this->render(['value' => Carbon::parse('2026-10-13 14:30:00')]);

        $this->assertStringContainsString('Oct 13, 2026 2:30:00 PM UTC', $html);
    }

    public function test_date_filters_cover_the_whole_mountain_day(): void
    {
        [$start, $end] = \App\Support\DisplayTime::dayBounds('2026-10-22');

        $this->assertSame('2026-10-22 06:00:00', $start->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-23 05:59:59', $end->format('Y-m-d H:i:s'));
    }

    public function test_a_bad_display_timezone_falls_back_instead_of_breaking(): void
    {
        config(['app.display_timezone' => 'Not/AZone']);
        $reset = new \ReflectionProperty(\App\Support\DisplayTime::class, 'resolved');
        $reset->setValue(null, null);

        $this->assertSame('America/Edmonton', \App\Support\DisplayTime::tz());

        $reset->setValue(null, null);
    }

    public function test_a_missing_timestamp_shows_the_fallback_and_no_time_element(): void
    {
        $html = $this->render(['value' => null, 'fallback' => 'Never']);

        $this->assertStringContainsString('Never', $html);
        $this->assertStringNotContainsString('<time', $html);
    }

    public function test_an_unparseable_value_degrades_instead_of_throwing(): void
    {
        $html = $this->render(['value' => 'not a date', 'fallback' => 'Unknown']);

        $this->assertStringContainsString('Unknown', $html);
    }

    public function test_the_browser_side_script_is_present_on_both_layouts(): void
    {
        // Alpine-rendered times (log drawer, order activity) are formatted by
        // this script, so both layouts must include it.
        foreach (['v2.layouts.app', 'portal.layouts.app'] as $layout) {
            $source = file_get_contents(
                resource_path('views/'.str_replace('.', '/', $layout).'.blade.php')
            );

            $this->assertStringContainsString(
                "@include('partials.local-time')",
                $source,
                "{$layout} must include the local-time script."
            );
        }
    }

    public function test_the_script_exposes_the_helper_alpine_templates_call(): void
    {
        // The activity log drawer and the order activity panels format inline
        // with window.localTime rather than through the component.
        $script = file_get_contents(resource_path('views/partials/local-time.blade.php'));

        $this->assertStringContainsString('window.localTime = localTime', $script);
    }

    /**
     * Order stop times are stored as pre-formatted local wall-clock strings
     * rather than UTC instants, so shifting them would move real pickup and
     * delivery windows. This pins that they were left alone.
     */
    public function test_order_stop_times_are_not_treated_as_utc_instants(): void
    {
        $source = file_get_contents(resource_path('views/portal/orders/show.blade.php'));

        $this->assertStringContainsString(
            "\$stop->start_time->format('M j, Y g:i A')",
            $source,
            'Stop times must keep rendering as stored; they are not UTC instants.'
        );
    }
}
