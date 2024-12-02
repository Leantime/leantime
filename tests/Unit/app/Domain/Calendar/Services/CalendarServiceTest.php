<?php

namespace Unit\app\Domain\Calendar\Services;

use Leantime\Core\Configuration\Environment;
use Leantime\Core\Language;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
use Leantime\Domain\Menu\Repositories\Menu;
use Leantime\Domain\Setting\Repositories\Setting;
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

        //Load class to be tested
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

    //Write tests below

    /**
     * Test GetMenuTypes method
     */
    public function test_get_i_cal_url()
    {

        //Sha is generated from id -1 and sessionpassword 123abc
        $sha = 'ba62fbd0d08f6607d6b3213dcccc1b50f4d82f19';
        $url = $this->calendar->getICalUrl(1);

        $this->assertEquals(BASE_URL.'/calendar/ical/secret_'.$sha, $url, 'hash is not correct');

    }
}
