<?php

namespace Unit\app\Domain\Calendar\Services;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Core\Language;
use Leantime\Core\Support\OutboundHttpClient;
use Leantime\Domain\Calendar\Events\CalendarEventDeleted;
use Leantime\Domain\Calendar\Events\CalendarEventUpdated;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
use Leantime\Domain\Calendar\Services\Calendar;
use Leantime\Domain\Setting\Repositories\Setting;
use Unit\TestCase;

/**
 * Calendar plugins (CalDAV, Google Calendar) keep remote calendars in sync from these events,
 * so every change to an event has to fire one, and a refused change must not.
 */
class CalendarEventsTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private const DISPATCHER_STATE = [
        'eventRegistry',
        'filterRegistry',
        'available_hooks',
        'patternMatchCache',
        'compiledPatternCache',
        'eventRegistryVersion',
        'filterRegistryVersion',
    ];

    private array $dispatcherSnapshot = [];

    /** @var array<int, object> */
    private array $received = [];

    protected function setUp(): void
    {
        parent::setUp();

        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach (self::DISPATCHER_STATE as $prop) {
            $this->dispatcherSnapshot[$prop] = $reflection->getProperty($prop)->getValue();
        }

        session(['userdata.id' => 1]);

        $this->received = [];
        foreach ([CalendarEventUpdated::class, CalendarEventDeleted::class] as $eventClass) {
            EventDispatcher::add_event_listener($eventClass, function (object $event): void {
                $this->received[] = $event;
            });
        }
    }

    protected function tearDown(): void
    {
        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach ($this->dispatcherSnapshot as $prop => $value) {
            $reflection->getProperty($prop)->setValue(null, $value);
        }

        parent::tearDown();
    }

    public function test_editing_an_event_fires_calendar_event_updated(): void
    {
        $service = $this->service([
            'getEvent' => fn () => ['id' => 5, 'userId' => 1],
            'editEvent' => fn () => null,
        ]);

        $this->assertTrue($service->editEvent(['id' => 5, 'description' => 'Standup']));

        $this->assertCount(1, $this->received);
        $this->assertInstanceOf(CalendarEventUpdated::class, $this->received[0]);
        $this->assertSame(5, $this->received[0]->eventId);
    }

    public function test_editing_someone_elses_event_fires_nothing(): void
    {
        $service = $this->service([
            'getEvent' => fn () => ['id' => 5, 'userId' => 2],
            'editEvent' => fn () => null,
        ]);

        $this->assertFalse($service->editEvent(['id' => 5, 'description' => 'Standup']));
        $this->assertSame([], $this->received);
    }

    public function test_moving_an_event_fires_calendar_event_updated(): void
    {
        $service = $this->service([
            'getEvent' => fn () => ['id' => 5, 'userId' => 1],
            'patch' => fn () => true,
        ]);

        $this->assertTrue($service->patch(5, ['dateFrom' => '2026-11-09 10:00:00']));

        $this->assertCount(1, $this->received);
        $this->assertInstanceOf(CalendarEventUpdated::class, $this->received[0]);
        $this->assertSame(5, $this->received[0]->eventId);
    }

    public function test_a_refused_move_fires_nothing(): void
    {
        $service = $this->service([
            'getEvent' => fn () => ['id' => 5, 'userId' => 2],
            'patch' => fn () => true,
        ]);

        $this->assertFalse($service->patch(5, ['dateFrom' => '2026-11-09 10:00:00']));
        $this->assertSame([], $this->received);
    }

    public function test_moving_a_missing_event_fires_nothing_even_for_an_admin(): void
    {
        // calendar.manage skips the owner check, and the repository's patch() reports success
        // without matching a row.
        $service = $this->service([
            'getEvent' => fn () => false,
            'patch' => fn () => true,
        ], canManage: true);

        $this->assertFalse($service->patch(5, ['dateFrom' => '2026-11-09 10:00:00']));
        $this->assertSame([], $this->received);
    }

    public function test_deleting_an_event_fires_calendar_event_deleted(): void
    {
        $service = $this->service(['delPersonalEvent' => fn () => 1]);

        $service->delEvent(5);

        $this->assertCount(1, $this->received);
        $this->assertInstanceOf(CalendarEventDeleted::class, $this->received[0]);
        $this->assertSame(5, $this->received[0]->eventId);
    }

    public function test_deleting_someone_elses_event_fires_nothing(): void
    {
        // delPersonalEvent only deletes the session user's own rows.
        $service = $this->service(['delPersonalEvent' => fn () => 0]);

        $service->delEvent(5);

        $this->assertSame([], $this->received);
    }

    /**
     * A Calendar service over a stubbed repository, for session user 1.
     */
    private function service(array $repositoryMethods, bool $canManage = false): Calendar
    {
        $service = new Calendar(
            calendarRepo: $this->make(CalendarRepository::class, $repositoryMethods),
            language: $this->make(Language::class),
            settingsRepo: $this->make(Setting::class),
            config: $this->make(Environment::class),
            outboundHttpClient: $this->make(OutboundHttpClient::class),
        );
        $service->setPermissionService($this->make(PermissionService::class, [
            'currentUserCan' => fn () => $canManage,
            'authorize' => fn () => null,
        ]));

        return $service;
    }
}
