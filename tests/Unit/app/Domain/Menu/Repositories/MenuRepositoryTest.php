<?php

namespace Unit\app\Domain\Menu\Repositories;

use Leantime\Core\Configuration\Environment;
use Leantime\Core\Language;
use Leantime\Core\Modules\Contracts\StoresProjectModules;
use Leantime\Core\Modules\Module;
use Leantime\Core\Modules\ModuleRegistry;
use Leantime\Core\Modules\ProjectModules;
use Leantime\Domain\Menu\Repositories\Menu;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Tickets\Services\Tickets;
use Unit\TestCase;

class MenuRepositoryTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * The test object
     *
     * @var Menu
     */
    protected $menu;

    /** @var array<int, array<string, bool>> Module switches per project, as the store returns them. */
    private array $moduleOverrides = [];

    protected function setUp(): void
    {

        parent::setUp();

        if (! defined('BASE_URL')) {
            define('BASE_URL', 'http://localhost');
        }

        // Mock classes
        $settingsRepo = $this->make(Setting::class);
        $language = $this->make(Language::class);
        $config = $this->make(Environment::class);
        $ticketService = $this->make(Tickets::class, [
            'getLastTicketViewUrl' => function () {
                return '';
            },
            'getLastTimelineViewUrl' => function () {
                return '';
            },
        ]);

        $moduleRegistry = new ModuleRegistry;
        foreach (['ideas', 'blueprints', 'wiki', 'files', 'timesheets'] as $moduleId) {
            $moduleRegistry->register(new Module(id: $moduleId, title: $moduleId, routes: [$moduleId]));
        }

        $moduleStore = $this->makeEmpty(StoresProjectModules::class, [
            'getModuleOverrides' => fn (int $projectId): array => $this->moduleOverrides[$projectId] ?? [],
        ]);

        // Load class to be tested
        $this->menu = new Menu(
            settingsRepo: $settingsRepo,
            language: $language,
            config: $config,
            ticketsService: $ticketService,
            projectModules: new ProjectModules($moduleRegistry, $moduleStore),
        );

    }

    protected function _after()
    {
        $this->menu = null;
    }

    // Write tests below

    public function test_get_default_menu_structure()
    {
        $expected = $this->menu::DEFAULT_MENU;
        $defaultStructure = $this->menu->getMenuStructure();

        // Menu structure checks if roles are set in a menu item and will disable a menu item if not allowed to see
        // User executing the test is not logged in, has no session so it being disabled is correct
        $this->menu->menuStructures[$expected][40]['submenu'][80]['type'] = 'disabled';
        $this->menu->menuStructures[$expected][30]['submenu'][30]['href'] = '/ideas/showBoards';

        $this->assertEquals($this->menu->menuStructures[$expected], $defaultStructure, 'Default menu structure does not match the expected structure');
    }

    public function test_items_of_modules_switched_off_in_the_current_project_are_hidden()
    {
        session(['currentProject' => 7]);
        $this->moduleOverrides[7] = ['wiki' => false];

        $menuStructure = $this->menu->getMenuStructure();

        $this->assertArrayNotHasKey(60, $menuStructure[40]['submenu'], 'Docs should be hidden');
        $this->assertArrayHasKey(70, $menuStructure[40]['submenu'], 'Files should stay');
    }

    public function test_a_submenu_whose_items_are_all_switched_off_is_hidden()
    {
        session(['currentProject' => 7]);
        $this->moduleOverrides[7] = ['ideas' => false, 'blueprints' => false];

        $menuStructure = $this->menu->getMenuStructure();

        $this->assertArrayNotHasKey(30, $menuStructure, 'The "Think" submenu should be gone');
        $this->assertArrayHasKey(10, $menuStructure);
    }

    public function test_module_switches_do_not_apply_to_other_projects_or_the_personal_menu()
    {
        session(['currentProject' => 8]);
        $this->moduleOverrides[7] = ['wiki' => false];

        $this->assertArrayHasKey(60, $this->menu->getMenuStructure()[40]['submenu']);

        session(['currentProject' => 7]);
        $this->moduleOverrides[7] = ['timesheets' => false];

        $this->assertArrayHasKey(15, $this->menu->getMenuStructure('personal'));
    }

    public function test_get_invalid_menu_structure()
    {
        $expected = [];
        $invalidMenuStructure = $this->menu->getMenuStructure('invalid');
        $this->assertEquals($expected, $invalidMenuStructure, 'Invalid menu structure does not match the expected structure');
    }

    public function test_get_filtered_menu_structure()
    {

        \Leantime\Core\Events\EventDispatcher::add_filter_listener('leantime.domain.menu.repositories.menu.getMenuStructure.menuStructures.company', function ($menu) {

            if (isset($menu[15]) && isset($menu[15]['submenu'])) {
                unset($menu[15]['submenu'][20]);
            }

            return $menu;

        }, 10);

        $fullMenuStructure = $this->menu->getMenuStructure('company');
        $this->assertIsArray($fullMenuStructure[15]['submenu']);

        $this->assertFalse(isset($fullMenuStructure[15]['submenu'][20]), 'menu item was not removed');

    }

    public function test_inject_new_project_menu_type()
    {

        \Leantime\Core\Events\EventDispatcher::add_filter_listener('leantime.domain.menu.repositories.menu.getMenuStructure.menuStructures', function ($menuStructure) {

            $testStructure = [
                10 => ['item' => 'myNewMenu', 'type' => 'item'],
            ];

            $menuStructure['testType'] = $testStructure;

            return $menuStructure;

        }, 10);

        $fullMenuStructure = $this->menu->getMenuStructure('testType');
        $this->assertIsArray($fullMenuStructure);
        $this->assertEquals('myNewMenu', $fullMenuStructure[10]['item']);

    }
}
