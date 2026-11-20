<?php

namespace Tests\Unit\Support;

use App\Support\PayPeriod;
use PHPUnit\Framework\TestCase;

class PayPeriodTest extends TestCase
{
    private function assertPeriod(string $date, int $startDay, string $start, string $end, ?string $tz = null): void
    {
        $p = PayPeriod::getDates($date, $startDay, $tz);

        $this->assertSame($start . ' 00:00:00', $p['start']->format('Y-m-d H:i:s'), "start for $date / day $startDay");
        $this->assertSame($end . ' 23:59:59', $p['end']->format('Y-m-d H:i:s'), "end for $date / day $startDay");
    }

    public function test_start_day_1_is_the_calendar_month(): void
    {
        $this->assertPeriod('2026-11-15', 1, '2026-11-01', '2026-11-30');
    }

    public function test_after_start_day_period_runs_into_next_month(): void
    {
        $this->assertPeriod('2026-11-30', 26, '2026-11-26', '2026-12-25');
    }

    public function test_before_start_day_period_started_last_month(): void
    {
        $this->assertPeriod('2026-11-10', 26, '2026-10-26', '2026-11-25');
    }

    public function test_start_day_itself_belongs_to_the_new_period(): void
    {
        $this->assertPeriod('2026-11-26', 26, '2026-11-26', '2026-12-25');
    }

    public function test_last_day_of_a_period(): void
    {
        $this->assertPeriod('2026-11-25', 26, '2026-10-26', '2026-11-25');
    }

    public function test_period_crossing_into_next_year(): void
    {
        $this->assertPeriod('2026-12-30', 26, '2026-12-26', '2027-01-25');
    }

    public function test_period_crossing_from_previous_year(): void
    {
        $this->assertPeriod('2026-01-10', 26, '2025-12-26', '2026-01-25');
    }

    public function test_jan_31_does_not_overflow_into_march(): void
    {
        $this->assertPeriod('2027-01-31', 26, '2027-01-26', '2027-02-25');
    }

    public function test_start_day_31_is_clamped_in_february(): void
    {
        $this->assertPeriod('2027-02-28', 31, '2027-02-28', '2027-03-30');
    }

    public function test_start_day_31_before_the_clamped_day(): void
    {
        $this->assertPeriod('2027-03-15', 31, '2027-02-28', '2027-03-30');
    }

    public function test_start_day_31_on_a_31_day_month(): void
    {
        $this->assertPeriod('2027-03-31', 31, '2027-03-31', '2027-04-29');
    }

    public function test_leap_year_february_with_start_day_30(): void
    {
        $this->assertPeriod('2028-02-29', 30, '2028-02-29', '2028-03-29');
    }

    public function test_dates_use_the_given_timezone(): void
    {
        $p = PayPeriod::getDates('2026-11-15', 1, 'Asia/Dubai');

        $this->assertSame('Asia/Dubai', $p['start']->getTimezone()->getName());
        // 00:00 in Dubai (UTC+4) is 20:00 UTC the day before.
        $this->assertSame('2026-10-31 20:00:00', $p['start']->copy()->utc()->format('Y-m-d H:i:s'));
    }
}