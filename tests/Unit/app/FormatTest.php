<?php

namespace Unit\app;

use Leantime\Core\Support\Format;
use Tests\DateTimeHelper;
use Tests\Language;
use Tests\MockObject;

class FormatTest extends \Codeception\Test\Unit
{

    /**
     * @var DateTimeHelper|MockObject
     */
    private $dateTimeHelperMock;

    /**
     * @var Language|MockObject
     */
    private $languageMock;

    protected function setUp(): void
    {
        $this->dateTimeHelperMock = $this->createMock(\Leantime\Core\Support\DateTimeHelper::class);
        $this->languageMock = $this->createMock(\Leantime\Core\Language::class);
        app()->instance(\Leantime\Core\Support\DateTimeHelper::class, $this->dateTimeHelperMock);
        app()->instance(\Leantime\Core\Language::class, $this->languageMock);
        $this->format = new Format('2022-01-01T00:00:00Z');
    }

    public function testDate(): void
    {
        $formattedDateString = 'Jan 1, 2022';
        $this->dateTimeHelperMock
            ->method('getFormattedDateStringFromISO')
            ->willReturn($formattedDateString);

        $this->assertSame($formattedDateString, $this->format->date());
    }

    public function testTime(): void
    {
        $formattedTimeString = '00:00 AM';
        $this->dateTimeHelperMock
            ->method('getFormattedTimeStringFromISO')
            ->willReturn($formattedTimeString);

        $this->assertSame($formattedTimeString, $this->format->time());
    }

    public function testTime24(): void
    {
        $formattedTimeString = '00:00';
        $this->dateTimeHelperMock
            ->method('get24HourTimestringFromISO')
            ->willReturn($formattedTimeString);

        $this->assertSame($formattedTimeString, $this->format->time24());
    }

    //Similarly you can add tests for other 'Format' class methods.
}
