<?php

namespace Unit\app\Domain\Calendar\Controllers;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Exceptions\MissingParameterException;
use Leantime\Core\Exceptions\NotFoundException;
use Leantime\Domain\Calendar\Controllers\Ical;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Spatie\IcalendarGenerator\Components\Calendar as IcalCalendar;
use Unit\TestCase;

/**
 * The public iCal feed route (#4018). A failure used to be swallowed into a redirect to the 404
 * page, which sent logged-out calendar apps on to the login page and left no trace of the cause.
 * A stale or wrong secret is expected (calendar apps keep polling) and answers 404 without logging.
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

    public function test_unexpected_failure_is_logged_and_answered_with_a_plain_404(): void
    {
        $failure = new \Exception('Could not build the feed');

        $calendarService = Mockery::mock(CalendarService::class);
        $calendarService->shouldReceive('getIcalByRequestToken')->andThrow($failure);

        Log::shouldReceive('error')->once()->with($failure);

        $response = $this->controllerWith($calendarService)->get(['act' => 'calendar.ical.wrong_user']);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertFalse($response->isRedirection());
    }

    /**
     * @return array<string, array{0: \Exception}>
     */
    public static function expectedMisses(): array
    {
        return [
            'hashes match no feed' => [new NotFoundException('Calendar could not be retrieved')],
            'malformed token' => [new MissingParameterException('iCal token must contain both an iCal hash and a user hash')],
        ];
    }

    /**
     * @dataProvider expectedMisses
     */
    public function test_stale_or_malformed_secret_answers_404_without_logging(\Exception $miss): void
    {
        $calendarService = Mockery::mock(CalendarService::class);
        $calendarService->shouldReceive('getIcalByRequestToken')->andThrow($miss);

        Log::shouldReceive('error')->never();

        $response = $this->controllerWith($calendarService)->get(['act' => 'calendar.ical.stale_secret']);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertFalse($response->isRedirection());
    }
}
