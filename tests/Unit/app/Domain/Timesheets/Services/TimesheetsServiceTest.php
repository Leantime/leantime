<?php

namespace Unit\app\Domain\Timesheets\Services;

use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
use Leantime\Domain\Timesheets\Services\Timesheets;
use Leantime\Domain\Users\Repositories\Users;
use Unit\TestCase;

class TimesheetsServiceTest extends TestCase
{
    private Timesheets $service;

    protected function setUp(): void
    {
        parent::setUp();

        $timesheetRepository = $this->createMock(TimesheetRepository::class);
        $userRepository = $this->createMock(Users::class);

        $this->service = new Timesheets($timesheetRepository, $userRepository);
    }

    public function test_parse_time_to_decimal_with_numeric_values(): void
    {
        self::assertSame(2.5, $this->service->parseTimeToDecimal('2.5'));
        self::assertSame(3.0, $this->service->parseTimeToDecimal(3));
    }

    public function test_parse_time_to_decimal_with_natural_language(): void
    {
        self::assertSame(2.0, $this->service->parseTimeToDecimal('2 hours'));
        self::assertSame(1.5, $this->service->parseTimeToDecimal('90 minutes'));
    }

    public function test_parse_time_to_decimal_with_jira_notation(): void
    {
        self::assertSame(59.5, $this->service->parseTimeToDecimal('1w 2d 3h 30m'));
    }

    public function test_parse_time_to_decimal_with_mixed_order(): void
    {
        self::assertSame(8.5, $this->service->parseTimeToDecimal('30m 1d'));
    }

    public function test_parse_time_to_decimal_handles_invalid_string(): void
    {
        self::assertSame(0.0, $this->service->parseTimeToDecimal('not-a-valid-duration'));
    }
}


