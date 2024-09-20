<?php

namespace Unit\app\Domain\Menu\Repositories;

use Codeception\Test\Unit;
use Leantime\Config\Config;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Core\Language;
use Leantime\Core\Theme;
use Leantime\Domain\Menu\Repositories\Menu;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Tickets\Services\Tickets;

class ThemeTest extends Unit
{

    use \Codeception\Test\Feature\Stub;

    /**
     * The test object
     *
     * @var Theme
     */
    protected $theme;

    protected $settingsRepoMock;

    protected $languageMock;

    protected $configMock;

    protected $appSettingsMock;

    protected function _before()
    {

        //Mock classes
        $this->settingsRepoMock =  $this->make(Setting::class, [

        ]);
        $this->languageMock = $this->make(Language::class, [

        ]);

        $this->configMock =  $this->make(Environment::class, [
            "primarycolor"=> "#123",
            "secondarycolor"=> "#123",

        ]);

        $this->appSettingsMock = $this->make(AppSettings::class, [
            "appVersion" => "123",

        ]);

        //Load class to be tested
        $this->theme = new Theme(
            settingsRepo: $this->settingsRepoMock,
            language: $this->languageMock,
            config:$this->configMock,
            appSettings:  $this->appSettingsMock
        );

    }

    protected function _after()
    {
        $this->theme = null;
    }

    //Write tests below

    /**
     * Test GetMenuTypes method
     */
    public function testGetDefaultColorSchemeWithColorEnvSet() {

        $colorScheme = $this->theme->getColorScheme();
        $this->assertEquals("companyColors", $colorScheme);

    }

    /**
     * Test GetMenuTypes method
     */
    public function testGetDefaultColorSchemeWithoutEnv() {

        $configMock =  $this->make(Environment::class, []);

        $theme = new Theme(
            settingsRepo: $this->settingsRepoMock,
            language: $this->languageMock,
            config: $configMock,
            appSettings:  $this->appSettingsMock
        );
        $colorScheme = $theme->getColorScheme();
        $this->assertEquals("themeDefault", $colorScheme);

    }
    
}
