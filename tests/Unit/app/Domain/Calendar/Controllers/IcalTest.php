<?php

namespace Unit\app\Domain\Calendar\Controllers;

use Illuminate\Support\Facades\Log;
use Leantime\Domain\Calendar\Controllers\Ical;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Spatie\IcalendarGenerator\Components\Calendar as IcalCalendar;
use Unit\TestCase;

/**
 * The public iCal feed route (#4018). A failure used to be swallowed into a redirect to the 404
 * page, which sent logged-out calendar apps on to the login page and left no trace of the cause.
 */
class IcalTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function controllerWith(CalendarService $calendarService): Ical
    {
        $controller = (new \ReflectionClass(Ical::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Ical::class, 'calendarService'))->setValue($controller, $calendarService);

        return $controller;
    }

    public function test_serves_the_feed_as_text_calendar(): void
    {
        $calendarService = Mockery::mock(CalendarService::class);
        $calendarService->shouldReceive('getIcalByRequestToken')
            ->with('', 'calendar.ical.cal_user')
            ->andReturn(IcalCalendar::create('Leantime'));

        $response = $this->controllerWith($calendarService)->get(['act' => 'calendar.ical.cal_user']);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringStartsWith('text/calendar', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('BEGIN:VCALENDAR', $response->getContent());
    }

    public function test_failure_is_logged_and_answered_with_a_plain_404(): void
    {
        $failure = new \Exception('Calendar could not be retrieved');

        $calendarService = Mockery::mock(CalendarService::class);
        $calendarService->shouldReceive('getIcalByRequestToken')->andThrow($failure);

        Log::shouldReceive('error')->once()->with($failure);

        $response = $this->controllerWith($calendarService)->get(['act' => 'calendar.ical.wrong_user']);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertFalse($response->isRedirection());
    }
}
