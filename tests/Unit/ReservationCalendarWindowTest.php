<?php

namespace Tests\Unit;

use App\Services\ReservationCalendarWindow;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class ReservationCalendarWindowTest extends TestCase
{
    public function test_week_starts_today_and_contains_seven_consecutive_dates(): void
    {
        $service = new ReservationCalendarWindow;
        $today = Carbon::parse('2026-09-26 15:00:00');

        $start = $service->start(null, $today);

        $this->assertSame('2026-09-26', $start->toDateString());
        $this->assertSame([
            '2026-09-26',
            '2026-09-27',
            '2026-09-28',
            '2026-09-29',
            '2026-09-30',
            '2026-10-01',
            '2026-10-02',
        ], $service->dates($start));
    }

    public function test_next_window_starts_seven_days_after_today(): void
    {
        $service = new ReservationCalendarWindow;
        $today = Carbon::parse('2026-09-26');
        $start = $service->start('2026-10-03', $today);
        $dates = $service->dates($start);

        $this->assertSame('2026-10-03', $dates[0]);
        $this->assertSame('2026-10-09', $dates[6]);
    }

    public function test_past_requested_date_is_clamped_to_today(): void
    {
        $service = new ReservationCalendarWindow;
        $today = Carbon::parse('2026-09-26');

        $start = $service->start('2026-09-19', $today);

        $this->assertSame('2026-09-26', $start->toDateString());
    }
}
