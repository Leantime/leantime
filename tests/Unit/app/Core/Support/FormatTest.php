<?php

namespace Unit\app\Core\Support;

use Carbon\CarbonImmutable;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Core\Support\Format;
use Tests\DateTimeHelper;
use Tests\Language;
use Tests\MockObject;
use Unit\TestCase;

class FormatTest extends TestCase
{
    /**
     * @var DateTimeHelper|MockObject
     */
    private $carbonMacrosMock;

    /**
     * @var Language|MockObject
     */
    private $languageMock;

    protected function setUp(): void
    {

        parent::setUp();

        $this->languageMock = $this->createMock(\Leantime\Infrastructure\i18n\Language::class);
        app()->instance(\Leantime\Core\Support\CarbonMacros::class, $this->carbonMacrosMock);
        app()->instance(\Leantime\Infrastructure\i18n\Language::class, $this->languageMock);

        // America Los_Angeles is UTC - 8 so all db times need to come back from UTC - 8 hours
        CarbonImmutable::mixin(new CarbonMacros(
            'America/Los_Angeles',
            'en-US',
            'm/d/Y',
            'h:i A'
        ));

    }

    public function test_date(): void
    {
        $formattedDateString = '12/31/2021';
        $dbDate = '2022-01-01 00:00:00';
        $format = new Format($dbDate, '');

        $this->assertSame($formattedDateString, $format->date());
    }

    public function test_time(): void
    {
        $formattedTimeString = '04:00 PM';
        $dbDate = '2022-01-01 00:00:00';
        $format = new Format($dbDate, '');

        $this->assertSame($formattedTimeString, $format->time());
    }

    public function test_time24(): void
    {
        $formattedTimeString = '16:00';
        $dbDate = '2022-01-01 00:00:00';
        $format = new Format($dbDate, '');

        $this->assertSame($formattedTimeString, $format->time24());

    }

    // Similarly you can add tests for other 'Format' class methods.
}
