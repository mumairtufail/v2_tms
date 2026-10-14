<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Timestamps are stored and rendered in UTC, so a dispatcher outside UTC was
 * reading every activity log hours away from what actually happened.
 *
 * The fix is to hand the browser a real instant and let it format in the
 * viewer's own timezone. These tests cover the server half of that contract:
 * that the instant goes out with its offset, that the markup the script hooks
 * into is present, and that the pre-script fallback is never blank.
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

    public function test_it_marks_itself_for_the_browser_to_upgrade(): void
    {
        $html = $this->render(['value' => Carbon::parse('2026-10-13 14:30:00'), 'format' => 'short']);

        $this->assertStringContainsString('data-local-time="short"', $html);
    }

    public function test_the_fallback_text_is_readable_before_the_script_runs(): void
    {
        // With JS off this is what a user sees, so it must be a real time and
        // must say which zone it is in.
        $html = $this->render(['value' => Carbon::parse('2026-10-13 14:30:00')]);

        $this->assertStringContainsString('Oct 13, 2026 2:30 PM UTC', $html);
    }

    public function test_a_date_only_fallback_does_not_claim_a_timezone(): void
    {
        $html = $this->render(['value' => Carbon::parse('2026-10-13 14:30:00'), 'format' => 'date']);

        $this->assertStringContainsString('Oct 13, 2026', $html);
        $this->assertStringNotContainsString('UTC', $html);
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
        // The component is inert without it, so a layout that forgets the
        // partial would quietly keep showing UTC.
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
