<?php

namespace Unit\app\Domain\Tickets\Support;

use Carbon\CarbonImmutable;
use Leantime\Domain\Tickets\Support\WorkStart;
use Unit\TestCase;

/**
 * The shared definition of Planned and Time-boxed, read from a to-do's Work Start. Work Starts
 * are stored in UTC, so every case is checked in timezones far from UTC as well.
 */
class WorkStartTest extends TestCase
{
    private const LA = 'America/Los_Angeles';      // UTC-8 in November

    private const KIRITIMATI = 'Pacific/Kiritimati'; // UTC+14

    private const PAGO_PAGO = 'Pacific/Pago_Pago';   // UTC-11

    /** Local midnight of $day in $timezone, as stored (UTC). */
    private static function storedMidnight(string $day, string $timezone): string
    {
        return CarbonImmutable::createFromFormat('!Y-m-d', $day, $timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
    }

    /** A local wall-clock time in $timezone, as stored (UTC). */
    private static function stored(string $local, string $timezone): string
    {
        return CarbonImmutable::createFromFormat('Y-m-d H:i', $local, $timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
    }

    public function test_local_midnight_with_no_end_is_date_only_in_every_timezone(): void
    {
        foreach ([self::LA, self::KIRITIMATI, self::PAGO_PAGO, 'UTC'] as $timezone) {
            $start = self::storedMidnight('2026-11-24', $timezone);

            $this->assertTrue(WorkStart::isDateOnly($start, null, $timezone), $timezone);
            $this->assertFalse(WorkStart::isTimeBoxed($start, null, $timezone), $timezone);
            $this->assertSame(['2026-11-24'], WorkStart::plannedDays($start, null, $timezone), $timezone);
        }
    }

    public function test_the_same_stored_value_is_timed_for_someone_in_another_timezone(): void
    {
        // Midnight in Los Angeles is 8:00 in UTC: a plan there, a time box for someone in UTC.
        $start = self::storedMidnight('2026-11-24', self::LA);

        $this->assertTrue(WorkStart::isDateOnly($start, null, self::LA));
        $this->assertFalse(WorkStart::isDateOnly($start, null, 'UTC'));
        $this->assertTrue(WorkStart::isTimeBoxed($start, null, 'UTC'));
    }

    public function test_an_end_equal_to_the_start_or_zero_dates_still_mean_date_only(): void
    {
        $start = self::storedMidnight('2026-11-24', self::LA);

        $this->assertTrue(WorkStart::isDateOnly($start, $start, self::LA));
        $this->assertTrue(WorkStart::isDateOnly($start, '', self::LA));
        $this->assertTrue(WorkStart::isDateOnly($start, '0000-00-00 00:00:00', self::LA));
    }

    public function test_a_span_between_local_midnights_is_date_only_and_excludes_its_end_day(): void
    {
        $start = self::storedMidnight('2026-11-24', self::KIRITIMATI);
        $end = self::storedMidnight('2026-11-27', self::KIRITIMATI);

        $this->assertTrue(WorkStart::isDateOnly($start, $end, self::KIRITIMATI));
        $this->assertSame(['2026-11-24', '2026-11-25', '2026-11-26'], WorkStart::plannedDays($start, $end, self::KIRITIMATI));
    }

    public function test_a_real_time_makes_it_time_boxed_and_still_planned_for_that_day(): void
    {
        $start = self::stored('2026-11-24 14:00', self::PAGO_PAGO);
        $end = self::stored('2026-11-24 15:30', self::PAGO_PAGO);

        $this->assertFalse(WorkStart::isDateOnly($start, $end, self::PAGO_PAGO));
        $this->assertTrue(WorkStart::isTimeBoxed($start, $end, self::PAGO_PAGO));
        $this->assertSame(['2026-11-24'], WorkStart::plannedDays($start, $end, self::PAGO_PAGO));
        $this->assertTrue(WorkStart::isPlannedForDay($start, $end, '2026-11-24', self::PAGO_PAGO));
    }

    public function test_a_timed_start_without_an_end_is_time_boxed(): void
    {
        $start = self::stored('2026-11-24 09:00', self::LA);

        $this->assertTrue(WorkStart::isTimeBoxed($start, null, self::LA));
        $this->assertSame(['2026-11-24'], WorkStart::plannedDays($start, null, self::LA));
    }

    public function test_a_midnight_start_with_a_timed_end_is_time_boxed(): void
    {
        $start = self::storedMidnight('2026-11-24', self::LA);
        $end = self::stored('2026-11-24 02:00', self::LA);

        $this->assertFalse(WorkStart::isDateOnly($start, $end, self::LA));
        $this->assertTrue(WorkStart::isTimeBoxed($start, $end, self::LA));
    }

    public function test_a_timed_block_ending_at_midnight_does_not_cover_the_next_day(): void
    {
        $start = self::stored('2026-11-24 22:00', self::LA);
        $end = self::storedMidnight('2026-11-25', self::LA);

        $this->assertSame(['2026-11-24'], WorkStart::plannedDays($start, $end, self::LA));
    }

    public function test_a_timed_block_over_several_days_covers_each_day(): void
    {
        $start = self::stored('2026-11-24 09:00', self::LA);
        $end = self::stored('2026-11-26 17:00', self::LA);

        $this->assertSame(['2026-11-24', '2026-11-25', '2026-11-26'], WorkStart::plannedDays($start, $end, self::LA));
    }

    public function test_no_work_start_means_not_planned(): void
    {
        foreach ([null, '', '0000-00-00 00:00:00', '1969-12-31 00:00:00'] as $empty) {
            $this->assertSame([], WorkStart::plannedDays($empty, null, self::LA));
            $this->assertFalse(WorkStart::isDateOnly($empty, null, self::LA));
            $this->assertFalse(WorkStart::isTimeBoxed($empty, null, self::LA));
            $this->assertFalse(WorkStart::isPlannedForDay($empty, null, '2026-11-24', self::LA));
        }
    }

    public function test_planned_for_day_accepts_a_date_read_in_the_persons_timezone(): void
    {
        $start = self::storedMidnight('2026-11-24', self::KIRITIMATI);

        // 20:00 UTC on Nov 23 is already Nov 24 in Kiritimati (UTC+14); 10:00 UTC on Nov 24 is Nov 25 there.
        $this->assertTrue(WorkStart::isPlannedForDay($start, null, CarbonImmutable::parse('2026-11-23 20:00', 'UTC'), self::KIRITIMATI));
        $this->assertFalse(WorkStart::isPlannedForDay($start, null, CarbonImmutable::parse('2026-11-24 10:00', 'UTC'), self::KIRITIMATI));
    }

    public function test_a_day_that_starts_at_one_because_daylight_saving_skips_midnight_is_date_only(): void
    {
        // On 2026-09-06 America/Santiago jumps from 00:00 to 01:00, so the day starts at 01:00.
        $tz = 'America/Santiago';
        $start = WorkStart::startOfLocalDay('2026-09-06', $tz);
        $this->assertSame('01:00', $start->format('H:i'));

        $stored = $start->setTimezone('UTC')->format('Y-m-d H:i:s');
        $this->assertTrue(WorkStart::isDateOnly($stored, null, $tz));
        $this->assertFalse(WorkStart::isTimeBoxed($stored, null, $tz));
        $this->assertSame(['2026-09-06'], WorkStart::plannedDays($stored, null, $tz));
    }

    public function test_a_span_across_a_skipped_midnight_keeps_every_day(): void
    {
        $tz = 'America/Santiago';
        $start = WorkStart::startOfLocalDay('2026-09-05', $tz)->setTimezone('UTC')->format('Y-m-d H:i:s');
        $end = WorkStart::startOfLocalDay('2026-09-08', $tz)->setTimezone('UTC')->format('Y-m-d H:i:s');

        $this->assertTrue(WorkStart::isDateOnly($start, $end, $tz));
        $this->assertSame(['2026-09-05', '2026-09-06', '2026-09-07'], WorkStart::plannedDays($start, $end, $tz));
    }

    public function test_checking_a_day_leaves_a_mutable_date_unchanged(): void
    {
        $start = self::storedMidnight('2026-11-24', self::LA);
        $day = \Carbon\Carbon::parse('2026-11-24 20:00', 'UTC');

        $this->assertTrue(WorkStart::isPlannedForDay($start, null, $day, self::LA));
        $this->assertSame('UTC', $day->getTimezone()->getName());
        $this->assertSame('2026-11-24 20:00', $day->format('Y-m-d H:i'));
    }

    public function test_bad_data_never_loops_past_the_cap(): void
    {
        $start = self::storedMidnight('2000-01-01', 'UTC');
        $end = self::storedMidnight('2030-01-01', 'UTC');

        $this->assertCount(WorkStart::MAX_DAYS, WorkStart::plannedDays($start, $end, 'UTC'));
    }

    public function test_start_of_local_day_accepts_only_real_days(): void
    {
        $start = WorkStart::startOfLocalDay('2026-11-24', self::LA);
        $this->assertNotNull($start);
        $this->assertSame('2026-11-24 08:00:00', $start->setTimezone('UTC')->format('Y-m-d H:i:s'));

        foreach (['2026-02-30', '2026-13-01', '24.11.2026', '2026-11-24 10:00', '', "2026-11-24\0"] as $bad) {
            $this->assertNull(WorkStart::startOfLocalDay($bad, self::LA), $bad);
        }
    }
}
