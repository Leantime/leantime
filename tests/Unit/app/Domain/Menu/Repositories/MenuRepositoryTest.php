<?php

namespace Unit\app\Domain\Menu\Repositories;

use Leantime\Core\Auth\Permissions\PermissionService;
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

    /** @var array<int, string>|null Permissions the current user holds; null = every permission. */
    private ?array $grantedPermissions = null;

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
            permissions: $this->make(PermissionService::class, [
                'currentUserCan' => fn (string $permissionKey): bool => $this->grantedPermissions === null
                    || in_array($permissionKey, $this->grantedPermissions, true),
            ]),
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

        // Every permission is granted here, so only the hrefFunction changes the structure
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

    public function test_items_need_the_permission_of_the_page_they_open(): void
    {
        $this->grantedPermissions = ['projects.edit', 'company.settings.view'];

        $companyMenu = $this->menu->getMenuStructure('company');

        $this->assertSame('item', $companyMenu[10]['submenu'][10]['type'], 'All Projects needs projects.edit');
        $this->assertSame('disabled', $companyMenu[10]['submenu'][5]['type'], 'All Timesheets needs timesheets.manage');
        $this->assertSame('disabled', $companyMenu[10]['submenu'][20]['type'], 'All Users needs users.view');
        $this->assertSame('item', $companyMenu[15]['submenu'][15]['type'], 'Company settings needs company.settings.view');
        $this->assertSame('disabled', $companyMenu[15]['submenu'][5]['type'], 'Apps need plugins.manage');
    }

    public function test_a_submenu_without_a_permitted_item_is_hidden(): void
    {
        $this->grantedPermissions = ['timesheets.manage'];

        $companyMenu = $this->menu->getMenuStructure('company');

        $this->assertArrayHasKey(10, $companyMenu, 'Management stays for its one permitted item');
        $this->assertArrayNotHasKey(15, $companyMenu, 'Administration has no permitted item');
    }

    public function test_plugin_items_with_a_role_keep_the_minimum_role_rule(): void
    {
        \Leantime\Core\Events\EventDispatcher::add_filter_listener('leantime.domain.menu.repositories.menu.getMenuStructure.menuStructures.personal', function ($menu) {
            $menu[90] = ['type' => 'item', 'module' => 'notes', 'title' => 'Notes', 'href' => '/notes/show', 'role' => 'editor'];

            return $menu;
        }, 10);

        session(['userdata' => ['id' => 5, 'role' => 'editor']]);
        $this->assertSame('item', $this->menu->getMenuStructure('personal')[90]['type']);

        session(['userdata' => ['id' => 5, 'role' => 'commenter']]);
        $this->assertSame('disabled', $this->menu->getMenuStructure('personal')[90]['type']);
    }

    public function test_plugin_items_without_a_permission_keep_the_submenus_minimum_role(): void
    {
        \Leantime\Core\Events\EventDispatcher::add_filter_listener('leantime.domain.menu.repositories.menu.getMenuStructure.menuStructures.company', function ($menu) {
            $menu[15]['submenu'][90] = ['type' => 'item', 'module' => 'customfields', 'title' => 'Custom fields', 'href' => '/custom-fields/create'];

            return $menu;
        }, 10);

        $this->grantedPermissions = ['company.settings.view'];

        session(['userdata' => ['id' => 5, 'role' => 'editor']]);
        $this->assertSame('disabled', $this->menu->getMenuStructure('company')[15]['submenu'][90]['type'], 'Administration items stay admin-only');

        session(['userdata' => ['id' => 5, 'role' => 'admin']]);
        $this->assertSame('item', $this->menu->getMenuStructure('company')[15]['submenu'][90]['type']);
    }
}
