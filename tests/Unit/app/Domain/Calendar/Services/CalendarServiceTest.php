<?php

namespace Unit\app\Domain\Calendar\Services;

use Leantime\Core\Configuration\Environment;
use Leantime\Core\Exceptions\MissingParameterException;
use Leantime\Core\Language;
use Leantime\Core\Support\OutboundHttpClient;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
use Leantime\Domain\Menu\Repositories\Menu;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Tickets\Services\Tickets;
use Spatie\IcalendarGenerator\Components\Calendar as IcalCalendar;
use Unit\TestCase;

class CalendarServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected $calendarRepository;

    protected $language;

    protected $settingsRepository;

    protected $config;

    protected $calendar;

    /**
     * The test object
     *
     * @var Menu
     */
    protected $menu;

    protected function setUp(): void
    {

        parent::setUp();

        if (! defined('BASE_URL')) {
            define('BASE_URL', 'http://localhost');
        }

        $this->calendarRepository = $this->make(CalendarRepository::class);
        $this->language = $this->make(Language::class);
        $this->settingsRepository = $this->make(Setting::class, [
            'getSetting' => 'secret',
        ]);
        $this->config = $this->make(Environment::class, [
            'sessionPassword' => '123abc',
        ]);

        // Load class to be tested
        $this->calendar = new \Leantime\Domain\Calendar\Services\Calendar(
            calendarRepo: $this->calendarRepository,
            language: $this->language,
            settingsRepo: $this->settingsRepository,
            config: $this->config,
            outboundHttpClient: $this->make(\Leantime\Core\Support\OutboundHttpClient::class)

        );

    }

    protected function _after()
    {
        $this->calendar = null;
    }

    // Write tests below

    /**
     * Test GetMenuTypes method
     */
    public function test_get_i_cal_url()
    {

        // Sha is generated from id -1 and sessionpassword 123abc
        $sha = 'ba62fbd0d08f6607d6b3213dcccc1b50f4d82f19';
        $url = $this->calendar->getICalUrl(1);

        $this->assertEquals(BASE_URL.'/calendar/ical/secret_'.$sha, $url, 'hash is not correct');

    }

    /**
     * A token that does not split into exactly two hashes must throw.
     */
    public function test_get_ical_by_request_token_rejects_malformed_token()
    {
        $this->expectException(MissingParameterException::class);

        // No underscore -> only one part -> invalid.
        $this->calendar->getIcalByRequestToken('notavalidtoken');
    }

    /**
     * A token taken from the request id (no 3-part act) must parse into
     * userHash/calHash and route them to the repository correctly.
     */
    public function test_get_ical_by_request_token_parses_id_token_and_routes_hashes()
    {
        $capturedUserHash = null;
        $capturedCalHash = null;

        $calendarRepo = $this->make(CalendarRepository::class, [
            'getCalendarBySecretHash' => function (string $userHash, string $calHash) use (&$capturedUserHash, &$capturedCalHash) {
                $capturedUserHash = $userHash;
                $capturedCalHash = $calHash;

                return [
                    [
                        'id' => 1,
                        'title' => 'Event',
                        'description' => 'desc',
                        'dateFrom' => '2025-04-16 10:00:00',
                        'dateTo' => '2025-04-16 11:00:00',
                        'allDay' => false,
                        'eventType' => 'calendar',
                        'dateContext' => 'plan',
                        'url' => '',
                    ],
                ];
            },
        ]);

        $service = new \Leantime\Domain\Calendar\Services\Calendar(
            calendarRepo: $calendarRepo,
            language: $this->language,
            settingsRepo: $this->settingsRepository,
            config: $this->config,
            outboundHttpClient: $this->make(\Leantime\Core\Support\OutboundHttpClient::class)
        );

        // Token format is {icalHash}_{userHash}.
        $result = $service->getIcalByRequestToken('calhash123_userhash456');

        $this->assertInstanceOf(IcalCalendar::class, $result);
        $this->assertEquals('userhash456', $capturedUserHash, 'user hash should come from the second token segment');
        $this->assertEquals('calhash123', $capturedCalHash, 'cal hash should come from the first token segment');
    }

    /**
     * When the frontcontroller act value carries the token as its third
     * dot-separated segment it must take precedence over the id token.
     */
    public function test_get_ical_by_request_token_prefers_act_segment()
    {
        $capturedUserHash = null;
        $capturedCalHash = null;

        $calendarRepo = $this->make(CalendarRepository::class, [
            'getCalendarBySecretHash' => function (string $userHash, string $calHash) use (&$capturedUserHash, &$capturedCalHash) {
                $capturedUserHash = $userHash;
                $capturedCalHash = $calHash;

                return [
                    [
                        'id' => 1,
                        'title' => 'Event',
                        'description' => 'desc',
                        'dateFrom' => '2025-04-16 10:00:00',
                        'dateTo' => '2025-04-16 11:00:00',
                        'allDay' => false,
                        'eventType' => 'calendar',
                        'dateContext' => 'plan',
                        'url' => '',
                    ],
                ];
            },
        ]);

        $service = new \Leantime\Domain\Calendar\Services\Calendar(
            calendarRepo: $calendarRepo,
            language: $this->language,
            settingsRepo: $this->settingsRepository,
            config: $this->config,
            outboundHttpClient: $this->make(\Leantime\Core\Support\OutboundHttpClient::class)
        );

        // act = calendar.ical.{icalHash}_{userHash}; id token is ignored.
        $result = $service->getIcalByRequestToken('ignored', 'calendar.ical.actcal_actuser');

        $this->assertInstanceOf(IcalCalendar::class, $result);
        $this->assertEquals('actuser', $capturedUserHash, 'user hash should come from the act segment');
        $this->assertEquals('actcal', $capturedCalHash, 'cal hash should come from the act segment');
    }

    // ---- permission-engine authorization ---------------------------------

    /** Builds the service with a stubbed repo + PermissionService, as the session user (id 1). */
    private function makeServiceWithPermissions(
        CalendarRepository $repo,
        \Leantime\Core\Auth\Permissions\PermissionService $perms
    ): \Leantime\Domain\Calendar\Services\Calendar {
        session(['userdata.id' => 1]);

        $service = new \Leantime\Domain\Calendar\Services\Calendar(
            calendarRepo: $repo,
            language: $this->language,
            settingsRepo: $this->settingsRepository,
            config: $this->config,
            outboundHttpClient: $this->make(\Leantime\Core\Support\OutboundHttpClient::class)
        );
        $service->setPermissionService($perms);

        return $service;
    }

    /** PermissionService stub: currentUserCan returns the given value for every key. */
    private function permissions(bool $allow): \Leantime\Core\Auth\Permissions\PermissionService
    {
        return $this->make(\Leantime\Core\Auth\Permissions\PermissionService::class, [
            'currentUserCan' => fn () => $allow,
            'authorize' => fn () => null,
        ]);
    }

    public function test_patch_only_forwards_allowlisted_event_columns(): void
    {
        $patched = null;
        $repo = $this->make(CalendarRepository::class, [
            'getEvent' => fn () => ['id' => 5, 'userId' => 1],
            'patch' => function ($id, $params) use (&$patched) {
                $patched = [$id, $params];

                return true;
            },
        ]);
        $service = $this->makeServiceWithPermissions($repo, $this->permissions(false));

        $this->assertTrue($service->patch(5, ['id' => 99, 'userId' => 2, 'dateFrom' => '2026-01-01 10:00:00', 'act' => 'x']));
        $this->assertSame([5, ['dateFrom' => '2026-01-01 10:00:00']], $patched, 'id/userId must never reach the repository');
    }

    public function test_get_event_returns_own_event(): void
    {
        $repo = $this->make(CalendarRepository::class, [
            'getEvent' => fn () => ['id' => 5, 'userId' => 1, 'description' => 'mine'],
        ]);
        // can(MANAGE) = false, but the session user (1) owns the event.
        $service = $this->makeServiceWithPermissions($repo, $this->permissions(false));

        $this->assertSame(1, $service->getEvent(5)['userId']);
    }

    public function test_get_event_soft_denies_foreign_event_without_manage(): void
    {
        $repo = $this->make(CalendarRepository::class, [
            'getEvent' => fn () => ['id' => 5, 'userId' => 2, 'description' => 'someone else'],
        ]);
        // Event owned by user 2; session user 1 lacks calendar.manage → soft-deny.
        $service = $this->makeServiceWithPermissions($repo, $this->permissions(false));

        $this->assertFalse($service->getEvent(5));
    }

    public function test_get_event_allows_foreign_event_with_manage(): void
    {
        $repo = $this->make(CalendarRepository::class, [
            'getEvent' => fn () => ['id' => 5, 'userId' => 2, 'description' => 'someone else'],
        ]);
        // calendar.manage (admin+) is the cross-user override.
        $service = $this->makeServiceWithPermissions($repo, $this->permissions(true));

        $this->assertSame(2, $service->getEvent(5)['userId']);
    }

    public function test_get_external_calendar_ignores_passed_userid_and_uses_session(): void
    {
        $capturedUserId = null;
        $repo = $this->make(CalendarRepository::class, [
            'getExternalCalendar' => function ($id, $userId) use (&$capturedUserId) {
                $capturedUserId = $userId;

                return ['id' => $id, 'url' => 'https://example.com/cal.ics'];
            },
        ]);
        $service = $this->makeServiceWithPermissions($repo, $this->permissions(true));

        // Caller passes a FOREIGN userId (99); the service must query as the session user (1).
        $service->getExternalCalendar(7, 99);

        $this->assertSame(1, $capturedUserId, 'external calendar lookup must use the session user, not the passed id');
    }

    public function test_get_my_external_calendars_ignores_passed_userid_and_uses_session(): void
    {
        $capturedUserId = null;
        $repo = $this->make(CalendarRepository::class, [
            'getMyExternalCalendars' => function ($userId) use (&$capturedUserId) {
                $capturedUserId = $userId;

                return [];
            },
        ]);
        $service = $this->makeServiceWithPermissions($repo, $this->permissions(true));

        $service->getMyExternalCalendars(99);

        $this->assertSame(1, $capturedUserId, 'calendar list must use the session user, not the passed id');
    }

    public function test_rpc_surface_is_locked(): void
    {
        $reflect = fn (string $m) => (new \ReflectionMethod(\Leantime\Domain\Calendar\Services\Calendar::class, $m))->getDocComment();
        $isApi = fn (string $m) => ($d = $reflect($m)) !== false && preg_match('/^\s*\*\s*@api\b/m', $d) === 1;
        $gate = function (string $m): ?string {
            $attrs = (new \ReflectionMethod(\Leantime\Domain\Calendar\Services\Calendar::class, $m))
                ->getAttributes(\Leantime\Core\Auth\Permissions\RequiresPermission::class);

            return $attrs === [] ? null : $attrs[0]->newInstance()->permission;
        };

        // The iCal feed methods are served by the public hash-authed route — never RPC-callable.
        $this->assertFalse($isApi('getIcalByHash'), 'getIcalByHash must not be @api');
        $this->assertFalse($isApi('getIcalByRequestToken'), 'getIcalByRequestToken must not be @api');

        // Every @api method carries a calendar.* dispatch gate.
        $expected = [
            'getEvent' => 'calendar.view',
            'getExternalCalendar' => 'calendar.view',
            'getMyExternalCalendars' => 'calendar.view',
            'getCachedExternalCalendarContent' => 'calendar.view',
            'addEvent' => 'calendar.create',
            'addExternalCalendarUrl' => 'calendar.create',
            'editEvent' => 'calendar.edit',
            'editExternalCalendar' => 'calendar.edit',
            'patch' => 'calendar.edit',
            'delEvent' => 'calendar.delete',
            'deleteGCal' => 'calendar.delete',
        ];
        foreach ($expected as $method => $permission) {
            $this->assertTrue($isApi($method), "$method should stay @api");
            $this->assertSame($permission, $gate($method), "$method must carry the $permission gate");
        }
    }

    // ---- calendar feed robustness ----------------------------------------

    /**
     * Regression for #3536: a ticket with a valid planned start (editFrom) but an
     * empty/sentinel editTo used to throw in parseDbDateTime(), 500-ing the whole
     * "My Work" calendar feed and leaving the dashboard widget loading forever.
     * editTo must now be guarded and fall back to editFrom.
     */
    public function test_get_calendar_survives_ticket_with_empty_edit_to(): void
    {
        $repo = $this->make(CalendarRepository::class, [
            'getAll' => fn () => [],
        ]);

        $ticket = [
            'id' => 10,
            'headline' => 'Planned task',
            'description' => '',
            'projectId' => 3,
            'status' => 3,
            'dateToFinish' => '',                 // invalid -> due-date block is skipped
            'editFrom' => '2026-06-18 09:00:00',  // valid planned start
            'editTo' => '',                       // empty end date -> previously threw
        ];

        $tickets = $this->make(Tickets::class, [
            'getOpenUserTicketsThisWeekAndLater' => fn () => ['thisWeek' => ['tickets' => [$ticket]]],
            'getStatusLabels' => fn () => [],
        ]);
        app()->instance(Tickets::class, $tickets);

        $service = new \Leantime\Domain\Calendar\Services\Calendar(
            calendarRepo: $repo,
            language: $this->language,
            settingsRepo: $this->settingsRepository,
            config: $this->config,
            outboundHttpClient: $this->make(\Leantime\Core\Support\OutboundHttpClient::class)
        );

        $events = $service->getCalendar(1);

        $editEvents = array_values(array_filter(
            $events,
            fn ($event) => ($event['dateContext'] ?? null) === 'edit'
        ));

        $this->assertCount(1, $editEvents, 'the planned-edit event should still be produced (no exception)');
        $this->assertSame('2026-06-18 09:00:00', $editEvents[0]['dateFrom']);
        $this->assertSame(
            '2026-06-18 09:00:00',
            $editEvents[0]['dateTo'],
            'editTo should fall back to editFrom when empty'
        );
    }

    /**
     * Builds a Calendar service whose outbound client records each request it would send.
     *
     * @param  \ArrayObject  $sentRequests  Collects [url, options] pairs.
     */
    private function calendarWithOutboundClient(\ArrayObject $sentRequests, ?\Closure $answer = null): \Leantime\Domain\Calendar\Services\Calendar
    {
        $outboundClient = $this->make(OutboundHttpClient::class, [
            'get' => function (string $url, array $options) use ($sentRequests, $answer) {
                $sentRequests->append([$url, $options]);

                return $answer !== null ? $answer($url) : new \GuzzleHttp\Psr7\Response(200, [], 'BEGIN:VCALENDAR');
            },
        ]);

        return new \Leantime\Domain\Calendar\Services\Calendar(
            calendarRepo: $this->calendarRepository,
            language: $this->language,
            settingsRepo: $this->settingsRepository,
            config: $this->config,
            outboundHttpClient: $outboundClient,
        );
    }

    public function test_load_ical_url_fetches_through_the_pinned_outbound_client(): void
    {
        $sentRequests = new \ArrayObject;

        $content = $this->calendarWithOutboundClient($sentRequests)->loadIcalUrl('webcal://cal.example.test/feed.ics');

        $this->assertSame('BEGIN:VCALENDAR', $content);
        $this->assertCount(1, $sentRequests);
        $this->assertSame('https://cal.example.test/feed.ics', $sentRequests[0][0]);
        $this->assertSame('text/calendar', $sentRequests[0][1]['headers']['Accept']);
    }

    public function test_load_ical_url_reports_a_refused_url(): void
    {
        $sentRequests = new \ArrayObject;
        $calendar = $this->calendarWithOutboundClient($sentRequests, function () {
            throw new \InvalidArgumentException('refused');
        });

        $this->expectExceptionMessage('Refused to fetch iCal feed: URL failed SSRF safety check');

        $calendar->loadIcalUrl('http://169.254.169.254/latest/meta-data/');
    }

    public function test_load_ical_url_refuses_internal_address_with_the_real_client(): void
    {
        $handlerCalls = new \ArrayObject;
        $handler = new class($handlerCalls) extends \GuzzleHttp\Handler\CurlHandler
        {
            public function __construct(private \ArrayObject $calls) {}

            public function __invoke($request, array $options): never
            {
                $this->calls->append($request);

                throw new \LogicException('No request may be sent to a refused URL');
            }
        };

        $calendar = new \Leantime\Domain\Calendar\Services\Calendar(
            calendarRepo: $this->calendarRepository,
            language: $this->language,
            settingsRepo: $this->settingsRepository,
            config: $this->config,
            outboundHttpClient: new OutboundHttpClient($handler),
        );

        try {
            $calendar->loadIcalUrl('http://127.0.0.1:8080/admin.ics');
            $this->fail('An internal address must be refused');
        } catch (\Exception $e) {
            $this->assertStringContainsString('SSRF safety check', $e->getMessage());
        }

        $this->assertCount(0, $handlerCalls);
    }

    /**
     * A calendar service whose repository returns $events for the feed owner.
     *
     * @param  array<int, array<string, mixed>>  $events
     */
    private function calendarWithFeedEvents(array $events): \Leantime\Domain\Calendar\Services\Calendar
    {
        return new \Leantime\Domain\Calendar\Services\Calendar(
            calendarRepo: $this->make(CalendarRepository::class, ['getCalendarBySecretHash' => fn () => $events]),
            language: $this->language,
            settingsRepo: $this->settingsRepository,
            config: $this->config,
            outboundHttpClient: $this->make(OutboundHttpClient::class)
        );
    }

    public function test_ical_feed_emits_timed_events_in_utc(): void
    {
        // The feed owner's zone; a TZID-qualified local time would be read as floating (#3114).
        session(['usersettings.timezone' => 'Europe/Zurich']);

        $ics = $this->calendarWithFeedEvents([[
            'id' => 1, 'title' => 'Meeting', 'description' => '', 'url' => '',
            'dateFrom' => '2025-04-16 08:00:00', 'dateTo' => '2025-04-16 09:00:00',
            'allDay' => false, 'eventType' => 'calendar', 'dateContext' => 'plan',
        ]])->getIcalByRequestToken('cal_user')->get();

        $this->assertStringContainsString('DTSTART:20250416T080000Z', $ics);
        $this->assertStringContainsString('DTEND:20250416T090000Z', $ics);
        $this->assertStringNotContainsString('DTSTART;TZID', $ics);
    }

    public function test_ical_feed_keeps_all_day_events_on_the_owners_date(): void
    {
        session(['usersettings.timezone' => 'Europe/Zurich']);

        // Midnight 17 April in Zurich is 22:00 UTC on the 16th.
        $ics = $this->calendarWithFeedEvents([[
            'id' => 2, 'title' => 'Offsite', 'description' => '', 'url' => '',
            'dateFrom' => '2025-04-16 22:00:00', 'dateTo' => '2025-04-17 21:59:00',
            'allDay' => true, 'eventType' => 'calendar', 'dateContext' => 'plan',
        ]])->getIcalByRequestToken('cal_user')->get();

        $this->assertMatchesRegularExpression('/DTSTART[^:\\r\\n]*VALUE=DATE:20250417/', $ics);
    }
}
