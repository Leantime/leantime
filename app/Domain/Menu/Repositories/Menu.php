<?php

/**
 * menu class - Menu definitions
 */

namespace Leantime\Domain\Menu\Repositories;

use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Modules\ProjectModules;
use Leantime\Core\UI\UiState;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;

class Menu
{
    use DispatchesEvents;

    // Default menu
    public const DEFAULT_MENU = 'default';

    // Menus that aren't about the current project, so project module switches don't apply to them
    private const NON_PROJECT_MENUS = ['personal', 'company', 'projecthub'];

    // Menu structures
    public array $menuStructures = [
        'default' => [
            5 => ['type' => 'item', 'module' => 'dashboard', 'title' => 'menu.overview', 'icon' => 'fa fa-fw fa-gauge-high', 'tooltip' => 'menu.overview_tooltip', 'href' => '/dashboard/show', 'active' => ['show']],
            10 => [
                'type' => 'submenu', 'id' => 'materialize', 'title' => 'menu.make', 'visual' => 'open',
                'submenu' => [
                    15 => ['type' => 'item', 'module' => 'tickets', 'title' => 'menu.todos', 'icon' => 'fa fa-fw fa-thumb-tack', 'tooltip' => 'menu.todos_tooltip', 'href' => '', 'hrefFunction' => 'getTicketMenu', 'active' => ['showKanban', 'showAll', 'showTicket', 'showList']],
                    25 => ['type' => 'item', 'module' => 'tickets', 'title' => 'menu.milestones', 'icon' => 'fa fa-fw fa-chart-gantt', 'tooltip' => 'menu.milestones_tooltip', 'href' => '', 'hrefFunction' => 'getTimelineMenu', 'active' => ['roadmap', 'showAllMilestones', 'showProjectCalendar']],
                    40 => ['type' => 'item', 'module' => 'goalcanvas', 'title' => 'menu.goals', 'icon' => 'fa fa-fw fa-bullseye', 'tooltip' => 'menu.goals_tooltip', 'href' => '/goalcanvas/dashboard', 'active' => ['showCanvas', 'dashboard']],
                ],
            ],
            30 => [
                'type' => 'submenu', 'id' => 'understand', 'title' => 'menu.think', 'visual' => 'closed',
                'submenu' => [
                    30 => ['type' => 'item', 'module' => 'ideas', 'title' => 'menu.ideas', 'icon' => 'fa fa-fw fa-lightbulb', 'tooltip' => 'menu.ideas_tooltip', 'href' => '', 'hrefFunction' => 'getIdeaMenu', 'active' => ['showBoards', 'advancedBoards']],
                    50 => ['type' => 'item', 'module' => 'blueprints', 'title' => 'menu.blueprints', 'icon' => 'fa fa-fw fa-compass-drafting', 'tooltip' => 'menu.blueprints_tooltip', 'href' => '/blueprints/showBoards', 'active' => ['showBoards', 'swot', 'lean', 'cp', 'dbm', 'ea', 'em', 'insights', 'lbm', 'minempathy', 'obm', 'risks', 'sb', 'sm', 'sq', 'value']],
                    70 => ['type' => 'item', 'module' => 'blueprints', 'title' => 'menu.retroscanvas', 'icon' => 'fa fa-fw fa-hand-spock', 'tooltip' => 'menu.retroscanvas_tooltip', 'href' => '/blueprints/retros/showCanvas', 'active' => ['retros']],
                ],
            ],
            40 => [
                'type' => 'submenu', 'id' => 'dataroom', 'title' => 'menu.dataroom', 'visual' => 'closed',
                'submenu' => [
                    60 => ['type' => 'item', 'module' => 'wiki', 'title' => 'menu.wiki', 'icon' => 'fa fa-fw fa-book', 'tooltip' => 'menu.wiki_tooltip', 'href' => '/wiki/show'],
                    70 => ['type' => 'item', 'module' => 'files', 'title' => 'menu.files', 'icon' => 'fa fa-fw fa-file', 'tooltip' => 'menu.files_tooltip', 'href' => '/files/browse'],
                    80 => ['type' => 'item', 'module' => 'reports', 'title' => 'menu.reports', 'icon' => 'fa fa-fw fa-chart-bar', 'tooltip' => 'menu.reports_tooltip', 'href' => '/reports/project', 'active' => ['project', 'show'], 'role' => 'editor'],
                ],
            ],
        ],
        'personal' => [
            5 => ['type' => 'item', 'module' => 'dashboard', 'title' => 'menu.sidemenu_home', 'icon' => 'fa fa-house', 'tooltip' => 'menu.overview_tooltip', 'href' => '/dashboard/home', 'active' => ['home']],
            7 => ['type' => 'item', 'module' => 'projects', 'title' => 'menu.sidemenu_my_project_hub', 'icon' => 'fa fa-solid fa-house-flag', 'tooltip' => 'menu.projecthub_tooltip', 'href' => '/projects/showMy', 'active' => ['showMy'], 'role' => 'editor'],
            15 => ['type' => 'item', 'module' => 'timesheets', 'title' => 'menu.sidemenu_my_timesheets', 'icon' => 'fa-clock', 'tooltip' => 'menu.my_timesheets_tooltip', 'href' => '/timesheets/showMy', 'active' => ['showMy']],
            20 => ['type' => 'item', 'module' => 'calendar', 'title' => 'menu.sidemenu_my_calendar', 'icon' => 'fa fa-calendar', 'tooltip' => 'menu.my_calendar_tooltip', 'href' => '/calendar/showMyCalendar', 'active' => ['showMyCalendar']],
        ],
        'projecthub' => [
            10 => ['type' => 'item', 'module' => 'projects', 'title' => 'menu.sidemenu_my_project_hub', 'icon' => 'fa-solid fa-house-flag', 'tooltip' => 'menu.my_projects_tooltip', 'href' => '/projects/showMy', 'active' => ['showMy']],
        ],
        'company' => [
            10 => [
                'type' => 'submenu', 'id' => 'Management', 'title' => 'menu.sidemenu_management', 'visual' => 'open', 'role' => 'manager',
                'submenu' => [
                    5 => ['type' => 'item', 'module' => 'timesheets', 'role' => 'manager', 'title' => 'menu.all_timesheets', 'icon' => 'fa fa-fw fa-business-time', 'tooltip' => 'menu.all_timesheets_tooltip', 'href' => '/timesheets/showAll', 'active' => ['showAll']],
                    10 => ['type' => 'item', 'module' => 'projects', 'role' => 'manager', 'title' => 'menu.all_projects', 'icon' => 'fa fa-fw fa-briefcase', 'tooltip' => 'menu.all_projects_tooltip', 'href' => '/projects/showAll', 'active' => ['showAll']],
                    15 => ['type' => 'item', 'module' => 'clients', 'role' => 'admin', 'title' => 'menu.all_clients', 'icon' => 'fa fa-fw fa-address-book', 'tooltip' => 'menu.all_clients_tooltip', 'href' => '/clients/showAll', 'active' => ['showAll']],
                    20 => ['type' => 'item', 'module' => 'users', 'role' => 'admin', 'title' => 'menu.all_users', 'icon' => 'fa fa-fw fa-users', 'tooltip' => 'menu.all_users_tooltip', 'href' => '/users/showAll', 'active' => ['showAll']],
                ],
            ],
            15 => [
                'type' => 'submenu', 'id' => 'administration', 'title' => 'menu.sidemenu_administration', 'visual' => 'open', 'role' => 'admin',
                'submenu' => [
                    5 => ['type' => 'item', 'module' => 'plugins', 'title' => 'menu.leantime_apps', 'icon' => 'fa fa-fw fa-puzzle-piece', 'tooltip' => 'menu.leantime_apps_tooltip', 'href' => '/plugins/marketplace', 'active' => ['marketplace', 'myapps']],
                    10 => ['type' => 'item', 'module' => 'connector', 'title' => 'menu.integrations', 'icon' => 'fa fa-fw fa-circle-nodes', 'tooltip' => 'menu.connector_tooltip', 'href' => '/connector/show', 'active' => ['show']],
                    15 => ['type' => 'item', 'module' => 'setting', 'title' => 'menu.company_settings', 'icon' => 'fa fa-fw fa-cogs', 'tooltip' => 'menu.company_settings_tooltip', 'href' => '/setting/editCompanySettings', 'active' => ['editCompanySettings']],

                ],
            ],
        ],
    ];

    public function __construct(
        /** @var SettingRepository */
        private SettingRepository $settingsRepo,
        /** @var LanguageCore */
        private LanguageCore $language,
        /** @var EnvironmentCore */
        private EnvironmentCore $config,
        /** @var TicketService */
        private TicketService $ticketsService,
        private ProjectModules $projectModules,
    ) {}

    /**
     * setSubmenuState - Save a UI state of the current user (submenu / accordion open or closed,
     * a sidebar width, a calendar view …).
     *
     * Stored per state in the database (UiState), not in the session: parallel requests can't
     * overwrite each other, and the next page load shows it.
     *
     * @param  string  $submenu  State name, e.g. "accordion_content-tickets-organization"
     * @param  string  $state  New value ("open", "closed", "320" …)
     */
    public function setSubmenuState(string $submenu, string $state): void
    {
        app(UiState::class)->set($submenu, $state);
    }

    /**
     * getSubmenuState - A saved UI state of the current user, or false when never saved.
     *
     * @param  string  $submenu  State name
     */
    public function getSubmenuState(string $submenu): string|false
    {
        return app(UiState::class)->get($submenu) ?? false;
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
                    if ($this->isModuleOff($element, $menuType)) {
                        unset($menuStructure[$key]);
                        break;
                    }

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
                        $submenuState = 'open';
                    } else {
                        $submenuState = app(UiState::class)->get($element['id']) ?? $element['visual'];
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
                                if ($this->isModuleOff($subelement, $menuType)) {
                                    unset($menuStructure[$key]['submenu'][$subkey]);
                                    break;
                                }

                                $this->processMenuItem($subelement, $menuStructure[$key]['submenu'][$subkey]);
                                break;

                            default:
                                exit("Cannot proceed due to invalid submenu element: '".$subelement['type']."'");
                        }
                    }

                    // A submenu whose items all belong to switched-off modules goes too
                    if (empty($menuStructure[$key]['submenu'])) {
                        unset($menuStructure[$key]);
                    }

                    break;

                default:
                    exit("Cannot proceed due to invalid menu element: '".$element['type']."'");
            }
        }

        // TODO: Add menu filter here!

        return $menuStructure;
    }

    /**
     * Whether a menu item belongs to a module that is switched off in the current project.
     *
     * @param  array<string, mixed>  $element  The menu item.
     * @param  string  $menuType  The menu being built; only project menus are filtered.
     */
    private function isModuleOff(array $element, string $menuType): bool
    {
        if (empty($element['module']) || in_array($menuType, self::NON_PROJECT_MENUS, true)) {
            return false;
        }

        return ! $this->projectModules->isEnabled((string) $element['module'], (int) session('currentProject'));
    }

    public function processMenuItem($element, &$structure): void
    {

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
        ];

        $sections = self::dispatch_filter('menuSections', $sections, ['currentRoute' => $currentRoute, 'default' => $default]);

        $result = $sections[$currentRoute] ?? $default;

        self::$sectionMenuTypeCache[$cacheKey] = $result;

        return $result;
    }
}
