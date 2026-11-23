<?php

namespace Unit\app\Domain\Notifications\Services;

use Carbon\CarbonImmutable;
use Leantime\Domain\Notifications\Services\FriendlyDate;
use Unit\TestCase;

/**
 * Notification dates read the way people say them: "Fri, Nov 28", "Yesterday 4:02 PM",
 * "Saturday 9:00 PM", and the year only when it isn't this year.
 */
class FriendlyDateTest extends TestCase
{
    private function now(): CarbonImmutable
    {
        // Monday, Nov 23, 2026, 10:00 in Los Angeles.
        return CarbonImmutable::create(2026, 11, 23, 10, 0, 0, 'America/Los_Angeles');
    }

    public function test_a_day_this_year_has_no_year(): void
    {
        $this->assertSame('Sat, Nov 28', FriendlyDate::day($this->now()->setDate(2026, 11, 28), $this->now()));
    }

    public function test_a_day_in_another_year_has_its_year(): void
    {
        $this->assertSame('Sat, Jan 9, 2027', FriendlyDate::day($this->now()->setDate(2027, 1, 9), $this->now()));
    }

    public function test_today_is_just_the_time(): void
    {
        $this->assertSame('8:05 AM', FriendlyDate::when($this->now()->setTime(8, 5), $this->now(), 'en-US', 'Yesterday %s'));
    }

    public function test_yesterday_uses_the_translated_pattern(): void
    {
        $this->assertSame('Yesterday 4:02 PM', FriendlyDate::when($this->now()->subDay()->setTime(16, 2), $this->now(), 'en-US', 'Yesterday %s'));
    }

    public function test_within_the_week_is_the_weekday(): void
    {
        $this->assertSame('Saturday 9:00 PM', FriendlyDate::when($this->now()->subDays(2)->setTime(21, 0), $this->now(), 'en-US', 'Yesterday %s'));
    }

    public function test_a_week_or_more_ago_is_the_date(): void
    {
        $this->assertSame('Mon, Nov 16, 9:05 AM', FriendlyDate::when($this->now()->subDays(7)->setTime(9, 5), $this->now(), 'en-US', 'Yesterday %s'));
    }

    public function test_day_names_follow_the_language(): void
    {
        $this->assertSame('samedi 9:00 PM', FriendlyDate::when($this->now()->subDays(2)->setTime(21, 0), $this->now(), 'fr-FR', 'Hier %s'));
    }
}
