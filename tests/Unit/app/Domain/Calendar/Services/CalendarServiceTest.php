<?php

namespace Unit\app\Domain\Menu\Repositories;

use Codeception\Test\Unit;
use Leantime\Config\Config;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Core\Language;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepo;
use Leantime\Domain\Calendar\Services\Calendar;
use Leantime\Domain\Menu\Repositories\Menu;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Tickets\Services\Tickets;

class CalendarServiceTest extends Unit
{

    use \Codeception\Test\Feature\Stub;

    protected $calendarRepo;

    protected $language;

    protected $settingsRepo;

    protected $config;

    protected $calendar;

    /**
     * The test object
     *
     * @var Menu
     */
    protected $menu;

    protected function _before()
    {

        $this->calendarRepo = $this->make(CalendarRepo::class);
        $this->language = $this->make(Language::class);
        $this->settingsRepo =  $this->make(Setting::class, [
            "getSetting" => "secret"
        ]);
        $this->config = $this->make(Environment::class, [
            "sessionPassword" => "123abc"
        ]);

        //Load class to be tested
        $this->calendar = new \Leantime\Domain\Calendar\Services\Calendar(
            calendarRepo: $this->calendarRepo,
            language: $this->language,
            settingsRepo: $this->settingsRepo,
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
    public function testGetICalUrl() {



        //Sha is generated from id -1 and sessionpassword 123abc
        $sha = "ba62fbd0d08f6607d6b3213dcccc1b50f4d82f19";
        $url = $this->calendar->getICalUrl(1);

        $this->assertEquals(BASE_URL . "/calendar/ical/secret_" . $sha, $url, 'hash is not correct');

    }


}
