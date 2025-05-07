<?php

namespace Tests\Unit\App\Core\Support;

use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidDateException;
use Carbon\Exceptions\InvalidFormatException;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Language;
use Leantime\Infrastructure\Support\CarbonMacros;
use Leantime\Infrastructure\Support\DateTimeHelper;
use Unit\TestCase;

class DateTimeHelperTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private DateTimeHelper $dateTimeHelper;

    private Environment $environmentMock;

    private Language $languageMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the Environment class
        $this->environmentMock = $this->make(Environment::class, [
            'defaultTimezone' => 'UTC',
            'language' => 'en-US',
        ]);
        app()->instance(Environment::class, $this->environmentMock);

        $this->languageMock = $this->createMock(Language::class);
        $this->languageMock->method('__')->willReturnCallback(function ($index) {
            $map = [
                'language.dateformat' => 'm/d/Y',
                'language.timeformat' => 'h:i A',
            ];

            return $map[$index] ?? null;
        });
        app()->instance(\Leantime\Core\Language::class, $this->languageMock);

        // Register mocks with the application container
        //
        //        app()->instance(Language::class, $this->languageMock);

        // America Los_Angeles is UTC - 8 so all db times need to come back from UTC - 8 hours
        CarbonImmutable::mixin(new CarbonMacros(
            'America/Los_Angeles',
            'en-US',
            'm/d/Y',
            'h:i A'
        ));

        // Create the DateTimeHelper instance
        $this->dateTimeHelper = new DateTimeHelper;
    }

    public function test_parse_iso8601_with_timezone_offset_midnight(): void
    {
        // Test ISO 8601 with timezone offset (2025-04-16T00:00:00-04:00)
        $dateString = '2025-04-16T00:00:00-04:00';
        $parsedDate = $this->dateTimeHelper->parseUserDateTime($dateString);

        $this->assertInstanceOf(CarbonImmutable::class, $parsedDate);
        $this->assertEquals('2025', $parsedDate->format('Y'));
        $this->assertEquals('04', $parsedDate->format('m'));
        $this->assertEquals('16', $parsedDate->format('d'));
        $this->assertEquals('00', $parsedDate->format('H'));
        $this->assertEquals('00', $parsedDate->format('i'));
        $this->assertEquals('00', $parsedDate->format('s'));
        $this->assertEquals('-04:00', $parsedDate->format('P'));
    }

    public function test_parse_iso8601_with_timezone_offset(): void
    {
        // Test ISO 8601 with timezone offset (2025-04-16T23:59:59-04:00)
        $dateString = '2025-04-16T23:59:59-04:00';
        $parsedDate = $this->dateTimeHelper->parseUserDateTime($dateString);

        $this->assertInstanceOf(CarbonImmutable::class, $parsedDate);
        $this->assertEquals('2025', $parsedDate->format('Y'));
        $this->assertEquals('04', $parsedDate->format('m'));
        $this->assertEquals('16', $parsedDate->format('d'));
        $this->assertEquals('23', $parsedDate->format('H'));
        $this->assertEquals('59', $parsedDate->format('i'));
        $this->assertEquals('59', $parsedDate->format('s'));
        $this->assertEquals('-04:00', $parsedDate->format('P'));
    }

    public function test_parse_iso8601_with_timezone_offset_hhmm(): void
    {
        // Test ISO 8601 with timezone offset (2025-04-16T23:59:59-0400)
        $dateString = '2025-04-16T23:59:59-0400';
        $parsedDate = $this->dateTimeHelper->parseUserDateTime($dateString);

        $this->assertInstanceOf(CarbonImmutable::class, $parsedDate);
        $this->assertEquals('2025', $parsedDate->format('Y'));
        $this->assertEquals('04', $parsedDate->format('m'));
        $this->assertEquals('16', $parsedDate->format('d'));
        $this->assertEquals('23', $parsedDate->format('H'));
        $this->assertEquals('59', $parsedDate->format('i'));
        $this->assertEquals('59', $parsedDate->format('s'));
        $this->assertEquals('-04:00', $parsedDate->format('P'));
    }

    public function test_parse_iso8601_with_timezone_offset_hh(): void
    {
        // Test ISO 8601 with timezone offset (2025-04-16T23:59:59-04)
        $dateString = '2025-04-16T23:59:59-04';
        $parsedDate = $this->dateTimeHelper->parseUserDateTime($dateString);

        $this->assertInstanceOf(CarbonImmutable::class, $parsedDate);
        $this->assertEquals('2025', $parsedDate->format('Y'));
        $this->assertEquals('04', $parsedDate->format('m'));
        $this->assertEquals('16', $parsedDate->format('d'));
        $this->assertEquals('23', $parsedDate->format('H'));
        $this->assertEquals('59', $parsedDate->format('i'));
        $this->assertEquals('59', $parsedDate->format('s'));
        $this->assertEquals('-04:00', $parsedDate->format('P'));
    }

    public function test_parse_iso8601_with_zulu_time(): void
    {
        // Test ISO 8601 with Z/Zulu time (2025-04-16T23:59:59Z)
        $dateString = '2025-04-16T23:59:59Z';
        $parsedDate = $this->dateTimeHelper->parseUserDateTime($dateString);

        $this->assertInstanceOf(CarbonImmutable::class, $parsedDate);
        $this->assertEquals('2025', $parsedDate->format('Y'));
        $this->assertEquals('04', $parsedDate->format('m'));
        $this->assertEquals('16', $parsedDate->format('d'));
        $this->assertEquals('23', $parsedDate->format('H'));
        $this->assertEquals('59', $parsedDate->format('i'));
        $this->assertEquals('59', $parsedDate->format('s'));
        // Z time should be parsed as UTC
        $this->assertEquals('+00:00', $parsedDate->format('P'));
    }

    public function test_parse_iso8601_without_timezone(): void
    {
        // Test ISO 8601 without timezone (2025-04-16T23:59:59)
        $dateString = '2025-04-16T23:59:59';
        $parsedDate = $this->dateTimeHelper->parseUserDateTime($dateString);

        $this->assertInstanceOf(CarbonImmutable::class, $parsedDate);
        $this->assertEquals('2025', $parsedDate->format('Y'));
        $this->assertEquals('04', $parsedDate->format('m'));
        $this->assertEquals('16', $parsedDate->format('d'));
        $this->assertEquals('23', $parsedDate->format('H'));
        $this->assertEquals('59', $parsedDate->format('i'));
        $this->assertEquals('59', $parsedDate->format('s'));
    }

    public function test_parse_user_date_format(): void
    {
        // Test parsing date in user format (m/d/Y)
        $dateString = '04/16/2025';
        $parsedDate = $this->dateTimeHelper->parseUserDateTime($dateString);

        $this->assertInstanceOf(CarbonImmutable::class, $parsedDate);
        $this->assertEquals('2025', $parsedDate->format('Y'));
        $this->assertEquals('04', $parsedDate->format('m'));
        $this->assertEquals('16', $parsedDate->format('d'));
    }

    public function test_parse_user_date_and_time_format(): void
    {
        // Test parsing date and time in user format (m/d/Y h:i A)
        $dateString = '04/16/2025';
        $timeString = '11:59 PM';
        $parsedDate = $this->dateTimeHelper->parseUserDateTime($dateString, $timeString);

        $this->assertInstanceOf(CarbonImmutable::class, $parsedDate);
        $this->assertEquals('2025', $parsedDate->format('Y'));
        $this->assertEquals('04', $parsedDate->format('m'));
        $this->assertEquals('16', $parsedDate->format('d'));
        $this->assertEquals('23', $parsedDate->format('H'));
        $this->assertEquals('59', $parsedDate->format('i'));
    }

    public function test_parse_user_date_with_start_of_day(): void
    {
        // Test parsing date with start of day
        $dateString = '04/16/2025';
        $parsedDate = $this->dateTimeHelper->parseUserDateTime($dateString, 'start');

        $this->assertInstanceOf(CarbonImmutable::class, $parsedDate);
        $this->assertEquals('2025', $parsedDate->format('Y'));
        $this->assertEquals('04', $parsedDate->format('m'));
        $this->assertEquals('16', $parsedDate->format('d'));
        $this->assertEquals('00', $parsedDate->format('H'));
        $this->assertEquals('00', $parsedDate->format('i'));
        $this->assertEquals('00', $parsedDate->format('s'));
    }

    public function test_parse_user_date_with_end_of_day(): void
    {
        // Test parsing date with end of day
        $dateString = '04/16/2025';
        $parsedDate = $this->dateTimeHelper->parseUserDateTime($dateString, 'end');

        $this->assertInstanceOf(CarbonImmutable::class, $parsedDate);
        $this->assertEquals('2025', $parsedDate->format('Y'));
        $this->assertEquals('04', $parsedDate->format('m'));
        $this->assertEquals('16', $parsedDate->format('d'));
        $this->assertEquals('23', $parsedDate->format('H'));
        $this->assertEquals('59', $parsedDate->format('i'));
        $this->assertEquals('59', $parsedDate->format('s'));
    }

    public function test_invalid_date_string(): void
    {
        // Test with invalid date string
        $this->expectException(InvalidFormatException::class);
        $this->dateTimeHelper->parseUserDateTime('not-a-date');
    }

    public function test_empty_date_string(): void
    {
        // Test with empty date string
        $this->expectException(InvalidDateException::class);
        $this->dateTimeHelper->parseUserDateTime('');
    }

    public function test_parse_db_date_time(): void
    {
        // Test parsing DB date time
        $dbDate = '2025-04-16 23:59:59';
        $parsedDate = $this->dateTimeHelper->parseDbDateTime($dbDate);

        $this->assertInstanceOf(CarbonImmutable::class, $parsedDate);
        $this->assertEquals('2025', $parsedDate->format('Y'));
        $this->assertEquals('04', $parsedDate->format('m'));
        $this->assertEquals('16', $parsedDate->format('d'));
        $this->assertEquals('23', $parsedDate->format('H'));
        $this->assertEquals('59', $parsedDate->format('i'));
        $this->assertEquals('59', $parsedDate->format('s'));
    }

    public function test_parse_user_24h_time(): void
    {
        // Test parsing 24h time
        $timeString = '23:59';
        $parsedTime = $this->dateTimeHelper->parseUser24hTime($timeString);

        $this->assertInstanceOf(CarbonImmutable::class, $parsedTime);
        $this->assertEquals('23', $parsedTime->format('H'));
        $this->assertEquals('59', $parsedTime->format('i'));
    }

    public function test_user_now(): void
    {
        // Test user now returns current time
        $now = $this->dateTimeHelper->userNow();
        $this->assertInstanceOf(CarbonImmutable::class, $now);

        // Should be within a few seconds of now
        $this->assertLessThan(5, abs(time() - $now->timestamp));
    }

    public function test_db_now(): void
    {
        // Test db now returns current time in UTC
        $now = $this->dateTimeHelper->dbNow();
        $this->assertInstanceOf(CarbonImmutable::class, $now);

        // Should be within a few seconds of now
        $this->assertLessThan(5, abs(time() - $now->timestamp));

        // Should be in UTC timezone
        $this->assertEquals('UTC', $now->timezone->getName());
    }

    public function test_is_valid_date_string(): void
    {
        // Test valid date strings
        $this->assertTrue($this->dateTimeHelper->isValidDateString('2025-04-16 23:59:59'));
        $this->assertTrue($this->dateTimeHelper->isValidDateString('2025-04-16T23:59:59-04:00'));

        // Test invalid date strings
        $this->assertFalse($this->dateTimeHelper->isValidDateString(''));
        $this->assertFalse($this->dateTimeHelper->isValidDateString(null));
        $this->assertFalse($this->dateTimeHelper->isValidDateString('1969-12-31 00:00:00'));
        $this->assertFalse($this->dateTimeHelper->isValidDateString('0000-00-00 00:00:00'));
    }
}
