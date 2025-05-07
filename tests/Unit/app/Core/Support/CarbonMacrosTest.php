<?php

namespace Tests\Unit\App\Core\Support;

use Carbon\CarbonImmutable;
use Leantime\Core\Language;
use Leantime\Infrastructure\Support\CarbonMacros;
use Unit\TestCase;

class CarbonMacrosTest extends TestCase
{
    private CarbonMacros $carbonMacros;

    private Language $languageMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->languageMock = $this->createMock(Language::class);
        $this->languageMock->method('__')
            ->willReturnCallback(function ($key) {
                return match ($key) {
                    'language.dateformat' => 'm/d/Y',
                    'language.timeformat' => 'h:i A',
                    'language.dayNamesShort' => 'zo,ma,di,wo,do,vr,za',
                    'language.dayNamesMin' => 'zo,ma,di,wo,do,vr,za',
                    'language.monthNamesShort' => 'jan,feb,mrt,apr,mei,jun,jul,aug,sep,okt,nov,dec',
                    default => $key
                };
            });

        app()->instance(Language::class, $this->languageMock);

        // Initialize with test values
        $this->carbonMacros = new CarbonMacros(
            userTimezone: 'America/Los_Angeles',
            userLanguage: 'en_US',
            userDateFormat: 'm/d/Y',
            userTimeFormat: 'h:i A',
            dbFormat: 'Y-m-d H:i:s',
            dbTimezone: 'UTC'
        );

        // Mix in the macros to CarbonImmutable
        CarbonImmutable::mixin($this->carbonMacros);
    }

    public function test_format_date_for_user(): void
    {
        $date = CarbonImmutable::create(2023, 12, 25, 14, 30, 0, 'UTC');
        $formatted = $date->formatDateForUser();

        // Should be formatted according to user's timezone (PST) and format (m/d/Y)
        $this->assertEquals('12/25/2023', $formatted);
    }

    public function test_format_time_for_user(): void
    {
        $date = CarbonImmutable::create(2023, 12, 25, 14, 30, 0, 'UTC');
        $formatted = $date->formatTimeForUser();

        // UTC 14:30 is 06:30 AM in PST
        $this->assertEquals('06:30 AM', $formatted);
    }

    public function test_format_24h_time_for_user(): void
    {
        $date = CarbonImmutable::create(2023, 12, 25, 14, 30, 0, 'UTC');
        $formatted = $date->format24HTimeForUser();

        // UTC 14:30 is 06:30 in PST
        $this->assertEquals('06:30', $formatted);
    }

    public function test_format_date_time_for_db(): void
    {
        // Create a date in user's timezone
        $date = CarbonImmutable::create(2023, 12, 25, 6, 30, 0, 'America/Los_Angeles');
        $formatted = $date->formatDateTimeForDb();

        // Should be converted to UTC and formatted as Y-m-d H:i:s
        $this->assertEquals('2023-12-25 14:30:00', $formatted);
    }

    public function test_set_to_user_timezone(): void
    {
        $date = CarbonImmutable::create(2023, 12, 25, 14, 30, 0, 'UTC');
        $converted = $date->setToUserTimezone();

        $this->assertEquals('America/Los_Angeles', $converted->timezone->getName());
        $this->assertEquals('06:30', $converted->format('H:i'));
    }

    public function test_set_to_db_timezone(): void
    {
        $date = CarbonImmutable::create(2023, 12, 25, 6, 30, 0, 'America/Los_Angeles');
        $converted = $date->setToDbTimezone();

        $this->assertEquals('UTC', $converted->timezone->getName());
        $this->assertEquals('14:30', $converted->format('H:i'));
    }

    public function test_dutch_language_support(): void
    {
        $macros = new CarbonMacros(
            userTimezone: 'Europe/Amsterdam',
            userLanguage: 'nl_NL',
            userDateFormat: 'd-m-Y',
            userTimeFormat: 'H:i',
            dbFormat: 'Y-m-d H:i:s',
            dbTimezone: 'UTC'
        );

        CarbonImmutable::mixin($macros);

        $date = CarbonImmutable::create(2023, 12, 25, 14, 30, 0, 'UTC');
        $formatted = $date->formatDateForUser();

        $this->assertEquals('25-12-2023', $formatted);
    }
}
