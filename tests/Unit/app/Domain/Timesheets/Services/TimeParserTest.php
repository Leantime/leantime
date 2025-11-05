<?php

namespace Unit\app\Domain\Timesheets\Services;

use InvalidArgumentException;
use Leantime\Domain\Timesheets\Services\TimeParser;
use Unit\TestCase;

class TimeParserTest extends TestCase
{
    private TimeParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new TimeParser();
    }

    public function test_parse_time_to_decimal_with_numeric_value(): void
    {
        $this->assertSame(2.5, $this->parser->parseTimeToDecimal('2.5'));
        $this->assertSame(3.0, $this->parser->parseTimeToDecimal(3));
    }

    public function test_parse_time_to_decimal_with_jira_format(): void
    {
        $this->assertSame(59.5, $this->parser->parseTimeToDecimal('1w 2d 3h 30m'));
    }

    public function test_parse_time_to_decimal_with_mixed_order(): void
    {
        $this->assertSame(8.5, $this->parser->parseTimeToDecimal('30m 1d'));
    }

    public function test_parse_time_to_decimal_with_natural_language(): void
    {
        $this->assertSame(2.0, $this->parser->parseTimeToDecimal('2 hours'));
        $this->assertSame(1.5, $this->parser->parseTimeToDecimal('90 minutes'));
    }

    public function test_parse_time_to_decimal_throws_exception_on_invalid_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->parser->parseTimeToDecimal('not-a-valid-duration');
    }

    public function test_parse_time_to_decimal_throws_exception_on_negative_values(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Time cannot be negative');

        $this->parser->parseTimeToDecimal('-2');
    }

    public function test_is_valid_time_input(): void
    {
        $this->assertTrue($this->parser->isValidTimeInput('1h 15m'));
        $this->assertFalse($this->parser->isValidTimeInput('bogus value'));
    }

    public function test_get_validation_error(): void
    {
        $this->assertNull($this->parser->getValidationError('45m'));

        $error = $this->parser->getValidationError('??');
        $this->assertIsString($error);
        $this->assertNotSame('', $error);
    }

    public function test_get_hours_and_days_configuration(): void
    {
        $this->assertSame(8, $this->parser->getHoursPerDay());
        $this->assertSame(5, $this->parser->getDaysPerWeek());
    }
}


