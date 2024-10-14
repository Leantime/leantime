<?php

namespace Unit\app\Domain\Menu\Repositories;

use Codeception\Test\Unit;
use Leantime\Config\Config;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Core\Language;
use Leantime\Domain\Menu\Repositories\Menu;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Tickets\Services\Tickets;



class MenuRepositoryTest extends Unit
{

    use \Codeception\Test\Feature\Stub;

    /**
     * The test object
     *
     * @var Menu
     */
    protected $menu;

    protected function _before()
    {

        //Mock classes
        $settingsRepo =  $this->make(Setting::class);
        $language = $this->make(Language::class);
        $config =  $this->make(Environment::class);
        $ticketService =  $this->make(Tickets::class, [
            'getLastTicketViewUrl' => function () { return ''; },
            'getLastTimelineViewUrl' => function () { return ''; },
        ]);



        //Load class to be tested
        $this->menu = new Menu(
            settingsRepo: $settingsRepo,
            language: $language,
            config:$config,
            ticketsService:$ticketService
        );


    }

    protected function _after()
    {
        $this->menu = null;
    }

    //Write tests below

    /**
     * Test GetMenuTypes method
     */
    public function testGetMenuTypes()
    {
        $result = $this->menu->getMenuTypes();

        // Assert that the result is an array
        $this->assertIsArray($result);

        // Assert that menu types have the expected keys
        $this->assertContains(Menu::DEFAULT_MENU, array_keys($result));

        // Further assertions can be done depending on use case and requirements
    }

    public function testGetDefaultMenuStructure()
    {
        $expected = $this->menu::DEFAULT_MENU;
        $defaultStructure = $this->menu->getMenuStructure();

        //Menu structure checks if roles are set in a menu item and will disable a menu item if not allowed to see
        //User executing the test is not logged in, has no session so it being disabled is correct
        $this->menu->menuStructures[$expected][10]['submenu'][60]['type'] = 'disabled';
        $this->menu->menuStructures[$expected][40]['submenu'][80]['type'] = 'disabled';
        $this->menu->menuStructures[$expected][30]['submenu'][30]['href'] = '/ideas/showBoards';



        $this->assertEquals($this->menu->menuStructures[$expected], $defaultStructure, 'Default menu structure does not match the expected structure');
    }

    public function testGetFullMenuStructure()
    {
        $expected = 'full_menu';
        $fullMenuStructure = $this->menu->getMenuStructure('full_menu');
        $this->menu->menuStructures[$expected][80]['submenu'][83]['type'] = 'disabled';

        $this->assertEquals($this->menu->menuStructures[$expected], $fullMenuStructure, 'Full menu structure does not match the expected structure');
    }

    public function testGetInvalidMenuStructure()
    {
        $expected = [];
        $invalidMenuStructure = $this->menu->getMenuStructure('invalid');
        $this->assertEquals($expected, $invalidMenuStructure, 'Invalid menu structure does not match the expected structure');
    }

    public function testGetFilteredMenuStructure()
    {

        \Leantime\Core\Events\EventDispatcher::add_filter_listener("leantime.domain.menu.repositories.menu.getMenuStructure.menuStructures.company", function($menu){

            if(isset($menu[15]) && isset($menu[15]['submenu'])) {
                unset($menu[15]['submenu'][20]);
            }

            return $menu;

        }, 10);

        $fullMenuStructure = $this->menu->getMenuStructure('company');
        $this->assertIsArray($fullMenuStructure[15]['submenu']);

        $this->assertFalse(isset($fullMenuStructure[15]['submenu'][20]), "menu item was not removed");

    }

    public function testInjectNewProjectMenuType()
    {

        \Leantime\Core\Events\EventDispatcher::add_filter_listener("leantime.domain.menu.repositories.menu.getMenuStructure.menuStructures", function($menuStructure){

            $testStructure = [
                10 => ["item" => "myNewMenu", "type"=>"item"]
            ];

            $menuStructure["testType"] = $testStructure;

            return $menuStructure;

        }, 10);

        $fullMenuStructure = $this->menu->getMenuStructure('testType');
        $this->assertIsArray($fullMenuStructure);
        $this->assertEquals("myNewMenu", $fullMenuStructure[10]["item"]);

    }
}
