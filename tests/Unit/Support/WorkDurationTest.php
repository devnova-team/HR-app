<?php

namespace Tests\Unit\Support;

use App\Support\WorkDuration;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class WorkDurationTest extends TestCase
{
    
    private function record(string $in, ?string $out, array $breaks = []): object
    {
        return (object) [
            'clock_in_at'  => Carbon::parse($in),
            'clock_out_at' => $out ? Carbon::parse($out) : null,
            'breaks'       => array_map(fn ($b) => (object) [
                'started_at' => Carbon::parse($b[0]),
                'ended_at'   => $b[1] ? Carbon::parse($b[1]) : null,
            ], $breaks),
        ];
    }

    public function test_null_record_is_zero(): void
    {
        $this->assertSame(0, WorkDuration::calculate(null));
    }

    public function test_full_day_without_breaks(): void
    {
        $r = $this->record('2026-11-02 09:00:00', '2026-11-02 17:00:00');

        $this->assertSame(28800, WorkDuration::calculate($r));
    }

    public function test_one_hour_break_is_subtracted(): void
    {
        $r = $this->record('2026-11-02 09:00:00', '2026-11-02 17:00:00', [
            ['2026-11-02 12:00:00', '2026-11-02 13:00:00'],
        ]);

        $this->assertSame(25200, WorkDuration::calculate($r));
    }

    public function test_two_breaks_are_both_subtracted(): void
    {
        $r = $this->record('2026-11-02 09:00:00', '2026-11-02 17:00:00', [
            ['2026-11-02 12:00:00', '2026-11-02 13:00:00'],
            ['2026-11-02 15:30:00', '2026-11-02 15:45:00'],
        ]);

        // 8h - 1h - 15m = 6h45m
        $this->assertSame(24300, WorkDuration::calculate($r));
    }

    public function test_open_record_counts_until_now(): void
    {
        $r = $this->record('2026-11-02 09:00:00', null);
        $now = Carbon::parse('2026-11-02 12:00:00');

        $this->assertSame(10800, WorkDuration::calculate($r, $now));
    }

    public function test_running_break_is_counted_until_now(): void
    {
        $r = $this->record('2026-11-02 09:00:00', null, [
            ['2026-11-02 11:00:00', null],
        ]);
        $now = Carbon::parse('2026-11-02 12:00:00');

        $this->assertSame(7200, WorkDuration::calculate($r, $now));
    }

    public function test_result_never_goes_below_zero(): void
    {
        $r = $this->record('2026-11-02 09:00:00', '2026-11-02 09:30:00', [
            ['2026-11-02 09:00:00', '2026-11-02 10:00:00'],
        ]);

        $this->assertSame(0, WorkDuration::calculate($r));
    }

    public function test_clock_out_before_clock_in_is_zero(): void
    {
        $r = $this->record('2026-11-02 17:00:00', '2026-11-02 09:00:00');

        $this->assertSame(0, WorkDuration::calculate($r));
    }
}