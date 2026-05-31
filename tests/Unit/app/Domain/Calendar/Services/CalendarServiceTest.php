<?php

namespace Unit\app\Domain\Calendar\Services;

use Leantime\Core\Configuration\Environment;
use Leantime\Core\Exceptions\MissingParameterException;
use Leantime\Core\Language;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
use Leantime\Domain\Menu\Repositories\Menu;
use Leantime\Domain\Setting\Repositories\Setting;
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
            config: $this->config

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
            config: $this->config
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
            config: $this->config
        );

        // act = calendar.ical.{icalHash}_{userHash}; id token is ignored.
        $result = $service->getIcalByRequestToken('ignored', 'calendar.ical.actcal_actuser');

        $this->assertInstanceOf(IcalCalendar::class, $result);
        $this->assertEquals('actuser', $capturedUserHash, 'user hash should come from the act segment');
        $this->assertEquals('actcal', $capturedCalHash, 'cal hash should come from the act segment');
    }
}
