<?php

/**
 * menu class - Menu definitions
 */

namespace Leantime\Domain\Menu\Repositories;

use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;

class Menu
{
    use DispatchesEvents;

    // Default menu
    public const DEFAULT_MENU = 'default';

    // Menu structures
    public array $menuStructures = [
        'clientportal' => [
            5 => ['type' => 'item', 'module' => 'clientportal', 'title' => 'menu.client_my_projects', 'icon' => 'fa fa-fw fa-folder-open', 'tooltip' => 'menu.client_my_projects', 'href' => '/clientportal/showDashboard', 'active' => ['showDashboard', 'showProject']],
        ],
        'admin' => [
            5 => ['type' => 'item', 'module' => 'dashboard',     'title' => 'menu.admin_dashboard',     'icon' => 'fa fa-fw fa-gauge-high',    'tooltip' => 'Admin Dashboard',         'href' => '/dashboard/adminHome',              'active' => ['adminHome'],   'role' => 'admin'],
            10 => ['type' => 'item', 'module' => 'projects',       'title' => 'menu.all_projects',        'icon' => 'fa fa-fw fa-briefcase',     'tooltip' => 'All Projects',            'href' => '/projects/showAll',                 'active' => ['showAll'],     'role' => 'admin'],
            20 => ['type' => 'item', 'module' => 'users',          'title' => 'menu.admin_users',         'icon' => 'fa fa-fw fa-users',         'tooltip' => 'User Management',         'href' => '/users/showAll',                    'active' => ['showAll', 'editUser', 'newUser'], 'role' => 'admin'],
            30 => ['type' => 'item', 'module' => 'clients',        'title' => 'menu.admin_clients',       'icon' => 'fa fa-fw fa-building',      'tooltip' => 'Client Organisations',    'href' => '/clients/showAll',                  'active' => ['showAll', 'newClient', 'showClient'], 'role' => 'admin'],
            50 => ['type' => 'item', 'module' => 'oneonone',       'title' => 'menu.admin_1on1',          'icon' => 'fa fa-fw fa-handshake',     'tooltip' => '1:1 Sessions',            'href' => '/oneonone/showTeam',                'active' => ['showTeam', 'showSession', 'newSession'], 'role' => 'admin'],
            60 => ['type' => 'item', 'module' => 'weekly-planning', 'title' => 'menu.admin_weekly_plans',  'icon' => 'fa fa-fw fa-calendar-week', 'tooltip' => 'Weekly Planning',         'href' => '/weekly-planning/showTeam',         'active' => ['showTeam', 'showPlan', 'newPlan', 'showBlockers', 'showCommitments'], 'role' => 'admin'],
            70 => ['type' => 'item', 'module' => 'timesheets',     'title' => 'menu.all_timesheets',      'icon' => 'fa fa-fw fa-business-time', 'tooltip' => 'All Timesheets',          'href' => '/timesheets/showAll',               'active' => ['showAll'],     'role' => 'admin'],
            90 => ['type' => 'separator'],
            95 => ['type' => 'item', 'module' => 'setting',        'title' => 'menu.company_settings',    'icon' => 'fa fa-fw fa-cogs',          'tooltip' => 'Company Settings',        'href' => '/setting/editCompanySettings',       'active' => ['editCompanySettings'], 'role' => 'admin'],
        ],
        'default' => [
            5 => ['type' => 'item', 'module' => 'dashboard', 'title' => 'menu.overview', 'icon' => 'fa fa-fw fa-gauge-high', 'tooltip' => 'menu.overview_tooltip', 'href' => '/dashboard/show', 'active' => ['show']],
            10 => ['type' => 'item', 'module' => 'tickets', 'title' => 'menu.todos', 'icon' => 'fa fa-fw fa-thumb-tack', 'tooltip' => 'menu.todos_tooltip', 'href' => '', 'hrefFunction' => 'getTicketMenu', 'active' => ['showKanban', 'showAll', 'showTicket', 'showList']],
            20 => ['type' => 'item', 'module' => 'tickets', 'title' => 'menu.milestones', 'icon' => 'fa fa-fw fa-chart-gantt', 'tooltip' => 'menu.milestones_tooltip', 'href' => '', 'hrefFunction' => 'getTimelineMenu', 'active' => ['roadmap', 'showAllMilestones', 'showProjectCalendar']],
            30 => ['type' => 'item', 'module' => 'goalcanvas', 'title' => 'menu.goals', 'icon' => 'fa fa-fw fa-bullseye', 'tooltip' => 'menu.goals_tooltip', 'href' => '/goalcanvas/dashboard', 'active' => ['showCanvas', 'dashboard']],
            40 => ['type' => 'item', 'module' => 'files', 'title' => 'menu.files', 'icon' => 'fa fa-fw fa-file', 'tooltip' => 'menu.files_tooltip', 'href' => '/files/browse'],
            50 => ['type' => 'item', 'module' => 'reports', 'title' => 'menu.reports', 'icon' => 'fa fa-fw fa-chart-bar', 'tooltip' => 'menu.reports_tooltip', 'href' => '/reports/show', 'role' => 'editor'],
            60 => [
                'type' => 'submenu', 'id' => 'advanced', 'title' => 'menu.advanced', 'visual' => 'closed',
                'submenu' => [
                    10 => ['type' => 'item', 'module' => 'ideas', 'title' => 'menu.ideas', 'icon' => 'fa fa-fw fa-lightbulb', 'tooltip' => 'menu.ideas_tooltip', 'href' => '', 'hrefFunction' => 'getIdeaMenu', 'active' => ['showBoards', 'advancedBoards']],
                    20 => ['type' => 'item', 'module' => 'strategy', 'title' => 'menu.blueprints', 'icon' => 'fa fa-fw fa-compass-drafting', 'tooltip' => 'menu.blueprints_tooltip', 'href' => '/strategy/showBoards', 'active' => ['showBoards']],
                    30 => ['type' => 'item', 'module' => 'retroscanvas', 'title' => 'menu.retroscanvas', 'icon' => 'fa fa-fw fa-hand-spock', 'tooltip' => 'menu.retroscanvas_tooltip', 'href' => '/retroscanvas/showCanvas'],
                    40 => ['type' => 'item', 'module' => 'wiki', 'title' => 'menu.wiki', 'icon' => 'fa fa-fw fa-book', 'tooltip' => 'menu.wiki_tooltip', 'href' => '/wiki/show'],
                ],
            ],
        ],
        // Display all menu items
        'full_menu' => [
            10 => [
                'type' => 'submenu', 'id' => 'planning', 'title' => 'menu.planning_execution', 'visual' => 'open',
                'submenu' => [
                    11 => ['type' => 'item', 'module' => 'dashboard', 'title' => 'menu.dashboard', 'icon' => 'fa fa-fw fa-home', 'tooltip' => 'menu.dashboard_tooltip', 'href' => '/dashboard/show', 'active' => ['show']],
                    21 => ['type' => 'item', 'module' => 'tickets', 'title' => 'menu.todos', 'icon' => 'fa fa-fw fa-thumb-tack', 'tooltip' => 'menu.todos_tooltip', 'href' => '', 'hrefFunction' => 'getTicketMenu', 'active' => ['showKanban', 'showAll', 'showTicket']],
                    31 => ['type' => 'item', 'module' => 'tickets', 'title' => 'menu.milestones', 'icon' => 'fa fa-fw fa-sliders', 'tooltip' => 'menu.milestones_tooltip', 'href' => '/tickets/roadmap', 'active' => ['roadmap']],
                    40 => ['type' => 'item', 'module' => 'goalcanvas', 'title' => 'menu.goals', 'icon' => 'fa fa-fw fa-bullseye', 'tooltip' => 'menu.goals_tooltip', 'href' => '/goalcanvas/showCanvas'],
                ],
            ],
            50 => [
                'type' => 'submenu', 'id' => 'dts-process', 'title' => 'menu.dts.process', 'visual' => 'closed',
                'submenu' => [
                    51 => ['type' => 'item', 'module' => 'insightscanvas', 'icon' => 'far fa-fw fa-note-sticky', 'tooltip' => 'menu.insightscanvas_tooltip', 'title' => 'menu.insightscanvas', 'href' => '/insightscanvas/showCanvas'],
                    52 => ['type' => 'item', 'module' => 'ideas', 'icon' => 'fa fa-fw fa-lightbulb', 'tooltip' => 'menu.ideas_tooltip', 'title' => 'menu.ideation', 'href' => '/ideas/showBoards'],
                ],
            ],
            60 => [
                'type' => 'submenu', 'id' => 'dts-frameworks',  'title' => 'menu.dts.frameworks', 'visual' => 'closed',
                'submenu' => [
                    61 => ['type' => 'header', 'title' => 'menu.dts.observe'],
                    62 => ['type' => 'item', 'module' => 'sbcanvas', 'title' => 'menu.sbcanvas', 'icon' => 'fas fa-fw fa-list-check', 'tooltip' => 'menu.sbcanvas_tooltip', 'href' => '/sbcanvas/showCanvas'],
                    63 => ['type' => 'item', 'module' => 'riskscanvas', 'title' => 'menu.riskscanvas', 'icon' => 'fas fa-fw fa-person-falling', 'tooltip' => 'menu.riskscanvas_tooltip', 'href' => '/riskscanvas/showCanvas'],
                    64 => ['type' => 'item', 'module' => 'eacanvas', 'title' => 'menu.eacanvas', 'icon' => 'fas fa-fw fa-tree', 'tooltip' => 'menu.eacanvas_tooltip', 'href' => '/eacanvas/showCanvas'],
                    65 => ['type' => 'header', 'title' => 'menu.dts.design'],
                    66 => ['type' => 'item', 'module' => 'lbmcanvas', 'title' => 'menu.lbmcanvas', 'icon' => 'fas fa-fw fa-building', 'tooltip' => 'menu.lbmcanvas_tooltip', 'href' => '/lbmcanvas/showCanvas'],
                    67 => ['type' => 'item', 'module' => 'dbmcanvas', 'title' => 'menu.dbmcanvas', 'icon' => 'fas fa-fw fa-building', 'tooltip' => 'menu.dbmcanvas_tooltip', 'href' => '/dbmcanvas/showCanvas'],
                    68 => ['type' => 'item', 'module' => 'cpcanvas', 'title' => 'menu.cpcanvas', 'icon' => 'fas fa-fw fa-city', 'tooltip' => 'menu.cpcanvas_tooltip', 'href' => '/cpcanvas/showCanvas'],
                    69 => ['type' => 'header', 'title' => 'menu.dts.validate'],
                    70 => ['type' => 'item', 'module' => 'sqcanvas', 'title' => 'menu.sqcanvas', 'icon' => 'fas fa-fw fa-chess', 'tooltip' => 'menu.sqcanvas_tooltip', 'href' => '/sqcanvas/showCanvas'],
                    71 => ['type' => 'item', 'module' => 'smcanvas', 'title' => 'menu.smcanvas', 'icon' => 'fas fa-fw fa-message', 'tooltip' => 'menu.smcanvas_tooltip', 'href' => '/smcanvas/showCanvas'],
                ],
            ],
            80 => [
                'type' => 'submenu', 'id' => 'dts-admin', 'title' => 'menu.dts.admin', 'visual' => 'open',
                'submenu' => [
                    81 => ['type' => 'item', 'module' => 'wiki', 'title' => 'menu.wiki', 'icon' => 'fa fa-fw fa-book', 'tooltip' => 'menu.wiki_tooltip', 'href' => '/wiki/show'],
                    82 => ['type' => 'item', 'module' => 'retroscanvas', 'title' => 'menu.retroscanvas', 'icon' => 'fa fa-fw fa-hand-spock', 'tooltip' => 'menu.retroscanvas_tooltip', 'href' => '/retroscanvas/showCanvas'],
                    83 => ['type' => 'item', 'module' => 'reports', 'title' => 'menu.reports', 'icon' => 'fa fa-fw fa-chart-bar', 'tooltip' => 'menu.reports_tooltip', 'href' => '/reports/show', 'role' => 'editor'],
                ],
            ],
        ],
        'personal' => [
            5 => ['type' => 'item', 'module' => 'dashboard', 'title' => 'menu.sidemenu_home', 'icon' => 'fa fa-fw fa-house', 'tooltip' => 'menu.overview_tooltip', 'href' => '/dashboard/home', 'active' => ['home']],
            10 => ['type' => 'item', 'module' => 'projects', 'title' => 'menu.sidemenu_my_project_hub', 'icon' => 'fa fa-fw fa-house-flag', 'tooltip' => 'menu.projecthub_tooltip', 'href' => '/projects/showMy', 'active' => ['showMy'], 'role' => 'editor'],
            15 => ['type' => 'item', 'module' => 'calendar', 'title' => 'menu.sidemenu_my_calendar', 'icon' => 'fa fa-fw fa-calendar', 'tooltip' => 'menu.my_calendar_tooltip', 'href' => '/calendar/showMyCalendar', 'active' => ['showMyCalendar']],
            30 => ['type' => 'item', 'module' => 'timesheets', 'title' => 'menu.sidemenu_my_timesheets', 'icon' => 'fa fa-fw fa-clock', 'tooltip' => 'menu.my_timesheets_tooltip', 'href' => '/timesheets/showMy', 'active' => ['showMy']],
            35 => ['type' => 'item', 'module' => 'oneonone', 'title' => 'menu.sidemenu_my_oneonones', 'icon' => 'fa fa-fw fa-handshake', 'tooltip' => 'menu.my_oneonones_tooltip', 'href' => '/oneonone/showMy', 'active' => ['showMy', 'showSession']],
            40 => ['type' => 'item', 'module' => 'weekly-planning', 'title' => 'menu.my_weekly_plan', 'icon' => 'fa fa-fw fa-calendar-week', 'tooltip' => 'menu.my_weekly_plan_tooltip', 'href' => '/weekly-planning/showMy', 'active' => ['showMy', 'showMyHistory'], 'role' => 'editor'],
        ],
        'projecthub' => [
            10 => ['type' => 'item', 'module' => 'projects', 'title' => 'menu.sidemenu_my_project_hub', 'icon' => 'fa-solid fa-house-flag', 'tooltip' => 'menu.my_projects_tooltip', 'href' => '/projects/showMy', 'active' => ['showMy']],
        ],
        'company' => [
            5 => ['type' => 'item', 'module' => 'dashboard',       'role' => 'teamlead', 'title' => 'menu.tlcm_home',          'icon' => 'fa fa-fw fa-house',          'tooltip' => 'menu.tlcm_home_tooltip',          'href' => '/dashboard/tlcmHome',                  'active' => ['tlcmHome']],
            10 => ['type' => 'item', 'module' => 'timesheets',      'role' => 'teamlead', 'title' => 'menu.all_timesheets',     'icon' => 'fa fa-fw fa-business-time',  'tooltip' => 'menu.all_timesheets_tooltip',     'href' => '/timesheets/showAll',                  'active' => ['showAll']],
            15 => ['type' => 'item', 'module' => 'oneonone',        'role' => 'teamlead', 'title' => 'menu.oneonone_sessions',   'icon' => 'fa fa-fw fa-handshake',      'tooltip' => 'menu.oneonone_sessions_tooltip',  'href' => '/oneonone/show',                       'active' => ['show', 'showTeam', 'showMy', 'showSession', 'newSession']],
            20 => ['type' => 'item', 'module' => 'weekly-planning', 'role' => 'teamlead', 'title' => 'menu.team_weekly_plans',  'icon' => 'fa fa-fw fa-calendar-week',  'tooltip' => 'menu.team_weekly_plans_tooltip',  'href' => '/weekly-planning/showTeam',            'active' => ['showTeam', 'showPlan', 'newPlan']],
            25 => ['type' => 'item', 'module' => 'weekly-planning', 'role' => 'teamlead', 'title' => 'menu.team_blockers',      'icon' => 'fa fa-fw fa-ban',            'tooltip' => 'menu.team_blockers_tooltip',      'href' => '/weekly-planning/showBlockers',        'active' => ['showBlockers']],
            30 => ['type' => 'item', 'module' => 'weekly-planning', 'role' => 'teamlead', 'title' => 'menu.team_commitments',   'icon' => 'fa fa-fw fa-handshake',      'tooltip' => 'menu.team_commitments_tooltip',   'href' => '/weekly-planning/showCommitments',     'active' => ['showCommitments']],
            35 => ['type' => 'item', 'module' => 'projects',        'role' => 'manager',  'title' => 'menu.all_projects',       'icon' => 'fa fa-fw fa-briefcase',      'tooltip' => 'menu.all_projects_tooltip',       'href' => '/projects/showAll',                    'active' => ['showAll']],
            40 => ['type' => 'item', 'module' => 'clients',         'role' => 'manager',  'title' => 'menu.all_clients',        'icon' => 'fa fa-fw fa-address-book',   'tooltip' => 'menu.all_clients_tooltip',        'href' => '/clients/showAll',                     'active' => ['showAll']],
            45 => ['type' => 'item', 'module' => 'users',           'role' => 'admin',    'title' => 'menu.all_users',          'icon' => 'fa fa-fw fa-users',          'tooltip' => 'menu.all_users_tooltip',          'href' => '/users/showAll',                       'active' => ['showAll']],
            50 => ['type' => 'separator'],
            55 => ['type' => 'item', 'module' => 'plugins',         'role' => 'admin',    'title' => 'menu.leantime_apps',      'icon' => 'fa fa-fw fa-puzzle-piece',   'tooltip' => 'menu.leantime_apps_tooltip',      'href' => '/plugins/marketplace',                 'active' => ['marketplace', 'myapps']],
            60 => ['type' => 'item', 'module' => 'connector',       'role' => 'admin',    'title' => 'menu.integrations',       'icon' => 'fa fa-fw fa-circle-nodes',   'tooltip' => 'menu.connector_tooltip',          'href' => '/connector/show',                      'active' => ['show']],
            65 => ['type' => 'item', 'module' => 'setting',         'role' => 'admin',    'title' => 'menu.company_settings',   'icon' => 'fa fa-fw fa-cogs',           'tooltip' => 'menu.company_settings_tooltip',   'href' => '/setting/editCompanySettings',         'active' => ['editCompanySettings']],
        ],
    ];

    /**
     * @param  AuthService  $authService
     */
    public function __construct(
        /** @var SettingRepository */
        private SettingRepository $settingsRepo,
        /** @var LanguageCore */
        private LanguageCore $language,
        /** @var EnvironmentCore */
        private EnvironmentCore $config,
        /** @var TicketService */
        private TicketService $ticketsService,
    ) {
        if (session()->exists('usersettings.submenuToggle') === false && session()->exists('userdata') === true) {
            $setting = $this->settingsRepo;
            session([
                'usersettings.submenuToggle' => unserialize(
                    $setting->getSetting('usersetting.'.session('userdata.id').'.submenuToggle')
                ),
            ]);
        }
    }

    /**
     * getMenuTypes - Return an array of a currently supported menu types
     *
     * @return array Array of supported menu types
     */
    public function getMenuTypes(): array
    {
        $language = $this->language;
        $config = $this->config;

        if (! isset($config->enableMenuType) || (isset($config->enableMenuType) && $config->enableMenuType === false)) {
            return [self::DEFAULT_MENU => $language->__('label.menu_type.'.self::DEFAULT_MENU)];
        }

        $menuTypes = [];

        foreach ($this->menuStructures as $key => $menu) {
            $menuTypes[$key] = $language->__("label.menu_type.$key");
        }

        return $menuTypes;
    }

    /**
     * setSubmenuState - Set the state of the submenu (open or closed)
     *
     * @param  string  $submenu  Submenu identifier
     * @param  string  $state  New state (open / closed)
     */
    public function setSubmenuState(string $submenu, string $state): void
    {

        if (session()->exists('usersettings.submenuToggle') && is_array(session('usersettings.submenuToggle')) && $submenu !== false) {
            session(['usersettings.submenuToggle.'.$submenu => $state]);
        }

        $setting = $this->settingsRepo;
        $setting->saveSetting('usersetting.'.session('userdata.id').'.submenuToggle', serialize(session('usersettings.submenuToggle')));
    }

    /**
     * getSubmenuState - Gets the state of the submenu (open or closed)
     *
     * @param  string  $submenu  Submenu identifier
     */
    public function getSubmenuState(string $submenu)
    {
        $setting = $this->settingsRepo;
        $subStructure = $setting->getSetting('usersetting.'.session('userdata.id').'.submenuToggle');

        session(['usersettings.submenuToggle' => unserialize($subStructure)]);

        return session('usersettings.submenuToggle.'.$submenu) ?? false;
    }

    /**
     * Builds the menu structure recursively.
     *
     * @param  array  &$menuStructure  The menu structure to build. Passed by reference.
     * @param  string  $filter  The filter to apply to the menu structure.
     * @return array The built menu structure.
     */
    protected function buildMenuStructure(array &$menuStructure, string $filter): array
    {

        foreach ($menuStructure as &$menuItem) {

            if ($menuItem['type'] !== 'submenu') {
                continue;
            }

            $menuItem['submenu'] = $this->buildMenuStructure($menuItem['submenu'], $filter);

            $filter = $filter.'.'.$menuItem['id'];

            return self::dispatch_filter(
                hook: $filter,
                payload: $menuItem['submenu'],
                function: 'getMenuStructure'
            );

        }

        return $menuStructure;
    }

    /**
     * getMenu - Return a specific menu structure
     *
     * @param  string  $menuType  Menu type to return
     * @return array Array of menu structrue
     */
    public function getMenuStructure(string $menuType = ''): array
    {

        if (empty($menuType)) {
            $menuType = self::DEFAULT_MENU;
        }

        $this->menuStructures = self::dispatch_filter(
            'menuStructures',
            $this->menuStructures,
            ['menuType' => $menuType]
        );

        // If menu structure cannot be found, don't return anything
        if (! isset($this->menuStructures[$menuType])) {
            return [];
        }

        $language = $this->language;
        $filter = "menuStructures.$menuType";

        $this->menuStructures[$menuType] = self::dispatch_filter(
            $filter,
            $this->menuStructures[$menuType],
            ['menuType' => $menuType]
        );

        $menuStructure = $this->menuStructures[$menuType];

        // When simple workflow mode is enabled, strip the 'advanced' submenu from the project menu
        // so that canvas, strategy, retros, and wiki tools stay out of the default view.
        // DB setting takes precedence over the LEAN_SIMPLE_WORKFLOW env default.
        $dbSimpleWorkflow = $this->settingsRepo->getSetting('companysettings.simpleWorkflow');
        $simpleWorkflowEnabled = $dbSimpleWorkflow !== false
            ? in_array($dbSimpleWorkflow, ['1', 'true', 1, true], true)
            : (bool) $this->config->simpleWorkflow;

        if ($simpleWorkflowEnabled && $menuType === 'default') {
            $menuStructure = array_filter(
                $menuStructure,
                static fn (array $item): bool => ! (isset($item['id']) && $item['id'] === 'advanced')
            );
        }

        if (session()->exists('usersettings.submenuToggle') === false || is_array(session('usersettings.submenuToggle')) === false) {
            session(['usersettings.submenuToggle' => []]);
        }

        ksort($menuStructure);

        foreach ($menuStructure as $key => $element) {
            if (isset($menuStructure[$key]['title'])) {
                $menuStructure[$key]['title'] = $language->__($element['title']);
            }

            switch ($element['type']) {
                case 'header':
                case 'separator':
                    break;

                case 'item':
                    // TO DO: Check if menu is enabled, e.g. `$moduleManagerRepo->isModuleEnabled($element['module'])`
                    $this->processMenuItem($element, $menuStructure[$key]);
                    break;

                case 'submenu':
                    if (isset($element['submenuFunction'])) {
                        if (method_exists($this, $this->{$element['submenuFunction']})) {
                            $menuStructure[$key]['submenu'] = $this->{$element['submenuFunction']}();
                        }
                    }

                    // Update menu toggle
                    if ($element['visual'] == 'always') {
                        $menuStructure[$key]['visual'] = 'open';
                    } else {
                        $submenuState = session('usersettings.submenuToggle.'.$element['id']) ?? $element['visual'];
                        session(['usersettings.submenuToggle.'.$element['id'] => $submenuState]);
                    }
                    $menuStructure[$key]['visual'] = $submenuState;

                    // Parse submenu
                    foreach ($element['submenu'] as $subkey => $subelement) {
                        ksort($menuStructure[$key]['submenu']);
                        $menuStructure[$key]['submenu'][$subkey]['title'] = $language->__($menuStructure[$key]['submenu'][$subkey]['title']);

                        switch ($subelement['type']) {
                            case 'header':
                                break;

                            case 'item':
                                $this->processMenuItem($subelement, $menuStructure[$key]['submenu'][$subkey]);
                                break;

                            default:
                                exit("Cannot proceed due to invalid submenu element: '".$subelement['type']."'");
                        }
                    }

                    break;

                default:
                    exit("Cannot proceed due to invalid menu element: '".$element['type']."'");
            }
        }

        // TODO: Add menu filter here!

        return $menuStructure;
    }

    public function processMenuItem($element, &$structure): void
    {

        // ModuleManager Check
        if (false) {
            $structure['type'] = 'disabled';

            return;
        }

        // Update security
        if (isset($element['role'])) {
            $accessGranted = AuthService::userIsAtLeast($element['role'], true);

            if (! $accessGranted) {
                $structure['type'] = 'disabled';
            }
        }

        if (isset($element['hrefFunction'])) {
            if (method_exists($this, $element['hrefFunction'])) {
                $structure['href'] = $this->{$element['hrefFunction']}();
            }
        }

    }

    /**
     * @return array|mixed|string|string[]
     */
    public function getTicketMenu(): mixed
    {

        $ticketService = $this->ticketsService;

        // Removing base URL from here since it is being added in the menu for loop in the template
        $base_url = ! empty($this->config->appUrl) ? $this->config->appUrl : BASE_URL;

        return str_replace($base_url, '', $ticketService->getLastTicketViewUrl());
    }

    public function getTimelineMenu(): mixed
    {

        $ticketService = $this->ticketsService;

        // Removing base URL from here since it is being added in the menu for loop in the template
        $base_url = ! empty($this->config->appUrl) ? $this->config->appUrl : BASE_URL;

        return str_replace($base_url, '', $ticketService->getLastTimelineViewUrl());
    }

    public function getIdeaMenu(): string
    {
        $url = '/ideas/showBoards';
        if (session()->exists('lastIdeaView')) {
            if (session('lastIdeaView') == 'kanban') {
                $url = '/ideas/advancedBoards';
            }
        }

        return $url;
    }

    /**
     * Request-level cache for getSectionMenuType results.
     * Prevents redundant computation when called from multiple composers (App, Menu, HeadMenu).
     * Keyed by route+default since different callers may pass different defaults.
     *
     * @var array<string, string>
     */
    private static array $sectionMenuTypeCache = [];

    public function getSectionMenuType($currentRoute, $default = 'default')
    {
        // Cache key includes both route and default since the result depends on both.
        // Different composers may pass different defaults for routes not in the sections map.
        $cacheKey = $currentRoute.'|'.$default;
        if (isset(self::$sectionMenuTypeCache[$cacheKey])) {
            return self::$sectionMenuTypeCache[$cacheKey];
        }

        $isAdmin = AuthService::userHasRole([\Leantime\Domain\Auth\Models\Roles::$owner, \Leantime\Domain\Auth\Models\Roles::$admin]);

        // Routes that always belong to the admin menu when accessed by an admin/owner
        $adminRoutes = [
            'dashboard.adminHome',
            'users.showAll', 'users.editUser', 'users.newUser', 'users.editOwn',
            'clients.showAll', 'clients.newClient', 'clients.showClient',
            'oneonone.show', 'oneonone.showMy', 'oneonone.showTeam', 'oneonone.showSession', 'oneonone.newSession',
            'timesheets.showAll',
            'projects.showAll',
            'weekly-planning.showTeam', 'weekly-planning.showPlan', 'weekly-planning.newPlan',
            'weekly-planning.showBlockers', 'weekly-planning.showCommitments',
            'setting.editCompanySettings',
        ];

        if ($isAdmin && in_array($currentRoute, $adminRoutes)) {
            $result = 'admin';
            self::$sectionMenuTypeCache[$cacheKey] = $result;

            return $result;
        }

        $sections = [
            'dashboard.home' => 'personal',
            'projects.showMy' => 'personal',
            'timesheets.showMy' => 'personal',
            'calendar.showMyCalendar' => 'personal',
            'calendar.showMyList' => 'personal',
            'tickets.roadmapAll' => 'personal',
            'notes.showNotes' => 'personal',
            'notes.showNotesList' => 'personal',
            'tickets.showAllMilestonesOverview' => 'personal',
            'users.editOwn' => 'personal',
            'dashboard.tlcmHome' => 'company',
            'oneonone.show' => 'company',
            'oneonone.showMy' => 'company',
            'oneonone.showTeam' => 'company',
            'oneonone.showSession' => 'company',
            'oneonone.newSession' => 'company',
            'setting.editCompanySettings' => 'company',
            'timesheets.showAll' => 'company',
            'projects.showAll' => 'company',
            'clients.showAll' => 'company',
            'clients.newClient' => 'company',
            'clients.showClient' => 'company',
            'users.showAll' => 'company',
            'users.editUser' => 'company',
            'plugins.show' => 'company',
            'plugins.marketplace' => 'company',
            'plugins.myapps' => 'company',
            'connector.show' => 'company',
            'connector.integration' => 'company',
            'billing.subscriptions' => 'company',
            'llamadorian.statusCollector' => 'personal',
            'weekly-planning.showTeam' => 'company',
            'weekly-planning.showPlan' => 'company',
            'weekly-planning.newPlan' => 'company',
            'weekly-planning.showBlockers' => 'company',
            'weekly-planning.showCommitments' => 'company',
            'weekly-planning.showMy' => 'personal',
            'weekly-planning.showMyHistory' => 'personal',
            'clientportal.showDashboard' => 'clientportal',
            'clientportal.showProject' => 'clientportal',
        ];

        $sections = self::dispatch_filter('menuSections', $sections, ['currentRoute' => $currentRoute, 'default' => $default]);

        $result = $sections[$currentRoute] ?? $default;

        self::$sectionMenuTypeCache[$cacheKey] = $result;

        return $result;
    }
}
