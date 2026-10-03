<?php

namespace Unit\app\Domain\Widgets\Hxcontrollers;

use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\UI\Template;
use Leantime\Domain\Api\Services\Api as ApiService;
use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
use Leantime\Domain\Widgets\Hxcontrollers\Calendar;
use Symfony\Component\HttpFoundation\Response;
use Unit\TestCase;

/**
 * The "hide done To-Dos" toggle (#3236) changes a saved preference, so it must only act on POST:
 * the Frontcontroller dispatches GET to custom actions too and the origin check skips GET.
 */
class CalendarToggleDoneTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * @param  \ArrayObject  $savedStates  Records [submenu, state] pairs persisted
     */
    private function controllerFor(string $method, \ArrayObject $savedStates, string|false $currentState = false): Calendar
    {
        $request = IncomingRequest::create('/widgets/calendar/toggleDone', $method);

        $tpl = $this->make(Template::class, [
            'getToggleState' => fn (string $name) => $currentState,
            'emptyResponse' => fn ($code = 200) => new Response('', $code),
            'assign' => fn () => null,
        ]);

        $apiService = $this->make(ApiService::class, [
            'setSubmenuState' => function (string $submenu, string $state) use ($savedStates): void {
                $savedStates->append([$submenu, $state]);
            },
        ]);

        $calendarService = $this->make(CalendarService::class, [
            'getMyExternalCalendars' => fn () => [],
            'getCalendar' => fn () => [],
        ]);

        $controller = (new \ReflectionClass(Calendar::class))->newInstanceWithoutConstructor();
        foreach (['incomingRequest' => $request, 'tpl' => $tpl] as $property => $value) {
            $reflection = new \ReflectionProperty(Calendar::class, $property);
            $reflection->setAccessible(true);
            $reflection->setValue($controller, $value);
        }
        $controller->init($calendarService, $apiService);

        return $controller;
    }

    public function test_get_is_rejected_without_changing_the_preference(): void
    {
        $savedStates = new \ArrayObject;

        $response = $this->controllerFor('GET', $savedStates)->toggleDone();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(405, $response->getStatusCode());
        $this->assertCount(0, $savedStates);
    }

    public function test_post_flips_the_preference(): void
    {
        $savedStates = new \ArrayObject;

        $this->assertNull($this->controllerFor('POST', $savedStates)->toggleDone());
        $this->assertNull($this->controllerFor('POST', $savedStates, 'hide')->toggleDone());

        $this->assertSame(
            [[Calendar::HIDE_DONE_TOGGLE, 'hide'], [Calendar::HIDE_DONE_TOGGLE, 'show']],
            $savedStates->getArrayCopy()
        );
    }
}
