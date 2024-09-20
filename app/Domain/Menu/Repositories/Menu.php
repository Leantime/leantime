<?php

/**
 * menu class - Menu definitions
 */

namespace Leantime\Domain\Menu\Repositories {

    use Leantime\Core\Configuration\Environment as EnvironmentCore;
    use Leantime\Core\Events\DispatchesEvents;
    use Leantime\Core\Language as LanguageCore;
    use Leantime\Domain\Auth\Services\Auth as AuthService;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;

    /**
     *
     */
    class Menu
    {
        use DispatchesEvents;

        // Default menu
        public const DEFAULT_MENU = 'default';

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
                        60 => ['type' => 'item', 'module' => 'whiteboards', 'title' => 'menu.whiteboards_premium', 'icon' => 'fa fa-solid fa-draw-polygon', 'tooltip' => 'Whiteboards', 'href' => '/plugins/marketplace#/plugins/details/leantime_whiteboardscanvas', 'role' => 'editor'],

                    ],
                ],
                30 => [
                    'type' => 'submenu', 'id' => 'understand', 'title' => 'menu.think', 'visual' => 'closed',
                    'submenu' => [
                        30 => ['type' => 'item', 'module' => 'ideas', 'title' => 'menu.ideas', 'icon' => 'fa fa-fw fa-lightbulb', 'tooltip' => 'menu.ideas_tooltip', 'href' => '', 'hrefFunction' => 'getIdeaMenu', 'active' => ['showBoards', 'advancedBoards']],
                        50 => ['type' => 'item', 'module' => 'strategy', 'title' => 'menu.blueprints', 'icon' => 'fa fa-fw fa-compass-drafting', 'tooltip' => 'menu.blueprints_tooltip', 'href' => '/strategy/showBoards', 'active' => ['showBoards']],
                        70 => ['type' => 'item', 'module' => 'retroscanvas', 'title' => 'menu.retroscanvas', 'icon' => 'fa fa-fw fa-hand-spock', 'tooltip' => 'menu.retroscanvas_tooltip', 'href' => '/retroscanvas/showCanvas'],
                    ],
                ],
                40 => [
                    'type' => 'submenu', 'id' => 'dataroom', 'title' => 'menu.dataroom', 'visual' => 'closed',
                    'submenu' => [
                        60 => ['type' => 'item', 'module' => 'wiki', 'title' => 'menu.wiki', 'icon' => 'fa fa-fw fa-book', 'tooltip' => 'menu.wiki_tooltip', 'href' => '/wiki/show'],
                        70 => ['type' => 'item', 'module' => 'files', 'title' => 'menu.files', 'icon' => 'fa fa-fw fa-file', 'tooltip' => 'menu.files_tooltip', 'href' => '/files/browse'],
                        80 => ['type' => 'item', 'module' => 'reports', 'title' => 'menu.reports', 'icon' => 'fa fa-fw fa-chart-bar', 'tooltip' => 'menu.reports_tooltip', 'href' => '/reports/show', 'role' => 'editor'],
                    ],
                ],
            ],
            //Display all menu items
            'full_menu' => [
                10 => [
                    'type' => 'submenu', 'id' => 'planning', 'title' => 'menu.planning_execution', 'visual' => 'open',
                    'submenu' => [
                        11 => ['type' => 'item', 'module' => 'dashboard', 'title' => 'menu.dashboard', 'icon' => 'fa fa-fw fa-home', 'tooltip' => 'menu.dashboard_tooltip', 'href' => '/dashboard/show', 'active' => ['show']],
                        21 => ['type' => 'item', 'module' => 'tickets', 'title' => 'menu.todos', 'icon' => 'fa fa-fw fa-thumb-tack', 'tooltip' => 'menu.todos_tooltip', 'href' => '', 'hrefFunction' => 'getTicketMenu', 'active' => ['showKanban', 'showAll', 'showTicket']],
                        31 => ['type' => 'item', 'module' => 'tickets', 'title' => 'menu.milestones', 'icon' => 'fa fa-fw fa-sliders', 'tooltip' => 'menu.milestones_tooltip','href' => '/tickets/roadmap', 'active' => ['roadmap']],
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
                5 => ['type' => 'item', 'module' => 'dashboard', 'title' => 'menu.sidemenu_home', 'icon' => 'fa fa-house', 'tooltip' => 'menu.overview_tooltip', 'href' => '/dashboard/home', 'active' => ['home']],
                7 => ['type' => 'item', 'module' => 'projects', 'title' => 'menu.sidemenu_my_project_hub', 'icon' => 'fa fa-solid fa-house-flag', 'tooltip' => 'menu.projecthub_tooltip', 'href' => '/projects/showMy', 'active' => ['showMy'], 'role' => 'editor'],

                10 => ['type' => 'item', 'module' => 'notes', 'title' => 'menu.notes_premium', 'icon' => 'fa fa-solid fa-notes-sticky', 'tooltip' => 'Notes', 'href' => '/plugins/marketplace#/plugins/details/leantime_notes', 'role' => 'editor'],

                15 => ['type' => 'item', 'module' => 'timesheets', 'title' => 'menu.sidemenu_my_timesheets', 'icon' => 'fa-clock', 'tooltip' => 'menu.my_timesheets_tooltip', 'href' => '/timesheets/showMy', 'active' => ['showMy']],
                20 => ['type' => 'item', 'module' => 'calendar', 'title' => 'menu.sidemenu_my_calendar', 'icon' => 'fa fa-calendar', 'tooltip' => 'menu.my_calendar_tooltip', 'href' => '/calendar/showMyCalendar', 'active' => ['showMyCalendar']],
            ],
            'projecthub' => [
                10 => ['type' => 'item', 'module' => 'projects', 'title' => 'menu.sidemenu_my_project_hub', 'icon' => 'fa-solid fa-house-flag', 'tooltip' => 'menu.my_projects_tooltip', 'href' => '/projects/showMy', 'active' => ['showMy']],
            ],
            "company" => [
                10 => [
                    'type' => 'submenu', 'id' => 'Management', 'title' => 'menu.sidemenu_management', 'visual' => 'open', 'role' => 'manager',
                    'submenu' => [
                        5 => ['type' => 'item', 'module' => 'timesheets', 'title' => 'menu.all_timesheets', 'icon' => 'fa fa-fw fa-business-time', 'tooltip' => 'menu.all_timesheets_tooltip', 'href' => '/timesheets/showAll', 'active' => ['showAll']],
                        10 => ['type' => 'item', 'module' => 'projects', 'title' => 'menu.all_projects', 'icon' => 'fa fa-fw fa-briefcase', 'tooltip' => 'menu.all_projects_tooltip', 'href' => '/projects/showAll', 'active' => ['showAll']],
                        15 => ['type' => 'item', 'module' => 'clients', 'title' => 'menu.all_clients', 'icon' => 'fa fa-fw fa-address-book', 'tooltip' => 'menu.all_clients_tooltip', 'href' => '/clients/showAll', 'active' => ['showAll']],
                        20 => ['type' => 'item', 'module' => 'users', 'title' => 'menu.all_users', 'icon' => 'fa fa-fw fa-users', 'tooltip' => 'menu.all_users_tooltip', 'href' => '/users/showAll', 'active' => ['showAll']],
                    ],
                ],
                15 => [
                    'type' => 'submenu', 'id' => 'administration', 'title' => 'menu.sidemenu_administration', 'visual' => 'open', 'role' => 'administrator',
                    'submenu' => [
                        5 => ['type' => 'item', 'module' => 'plugins', 'title' => 'menu.leantime_apps', 'icon' => 'fa fa-fw fa-puzzle-piece', 'tooltip' => 'menu.leantime_apps_tooltip', 'href' => '/plugins/marketplace', 'active' => ['marketplace', 'myapps']],
                        10 => ['type' => 'item', 'module' => 'connector', 'title' => 'menu.integrations', 'icon' => 'fa fa-fw fa-circle-nodes', 'tooltip' => 'menu.connector_tooltip', 'href' => '/connector/show', 'active' => ['show']],
                        15 => ['type' => 'item', 'module' => 'setting', 'title' => 'menu.company_settings', 'icon' => 'fa fa-fw fa-cogs', 'tooltip' => 'menu.company_settings_tooltip', 'href' => '/setting/editCompanySettings', 'active' => ['editCompanySettings']],
                        20 => ['type' => 'item', 'module' => 'notes', 'title' => 'menu.customfields_premium', 'icon' => 'fa fa-solid fa-list', 'tooltip' => 'Custom Fields', 'href' => '/plugins/marketplace#/plugins/details/leantime_customfields', 'role' => 'editor'],

                    ],
                ],
            ],
        ];


        /**
         * @param SettingRepository $settingsRepo
         * @param LanguageCore      $language
         * @param EnvironmentCore   $config
         * @param TicketService     $ticketsService
         * @param AuthService       $authService
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
            if (session()->exists("usersettings.submenuToggle") === false && session()->exists("userdata") === true) {
                $setting = $this->settingsRepo;
                session([
                    "usersettings.submenuToggle" => unserialize(
                        $setting->getSetting("usersetting." . session("userdata.id") . ".submenuToggle")
                    ),
                    ]);
            }
        }

        /**
         * getMenuTypes - Return an array of a currently supported menu types
         *
         * @access public
         * @return array  Array of supported menu types
         */
        public function getMenuTypes(): array
        {
            $language = $this->language;
            $config = $this->config;

            if (! isset($config->enableMenuType) || (isset($config->enableMenuType) && $config->enableMenuType === false)) {
                return [self::DEFAULT_MENU => $language->__('label.menu_type.' . self::DEFAULT_MENU)];
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
         * @access public
         * @param  string $submenu Submenu identifier
         * @param  string $state   New state (open / closed)
         */
        public function setSubmenuState(string $submenu, string $state): void
        {

            if (session()->exists("usersettings.submenuToggle") && is_array(session("usersettings.submenuToggle")) && $submenu !== false) {
                session(["usersettings.submenuToggle." . $submenu => $state]);
            }

            $setting = $this->settingsRepo;
            $setting->saveSetting("usersetting." . session("userdata.id") . ".submenuToggle", serialize(session("usersettings.submenuToggle")));
        }

        /**
         * getSubmenuState - Gets the state of the submenu (open or closed)
         *
         * @access public
         * @param  string $submenu Submenu identifier
         */
        public function getSubmenuState(string $submenu)
        {
            $setting = $this->settingsRepo;
            $subStructure = $setting->getSetting("usersetting." . session("userdata.id") . ".submenuToggle");

            session(["usersettings.submenuToggle" => unserialize($subStructure)]);

            return session("usersettings.submenuToggle." . $submenu) ?? false;
        }

        /**
         * Builds the menu structure recursively.
         *
         * @param array &$menuStructure The menu structure to build. Passed by reference.
         * @param string $filter The filter to apply to the menu structure.
         *
         * @return array The built menu structure.
         */
        protected function buildMenuStructure(array &$menuStructure, string $filter): array
        {

            foreach($menuStructure as &$menuItem) {

                if ($menuItem['type'] !== 'submenu') {
                    continue;
                }

                $menuItem['submenu'] = $this->buildMenuStructure($menuItem['submenu'], $filter);

                $filter = $filter . '.' . $menuItem['id'];

                return self::dispatch_filter(
                    hook: $filter,
                    payload:  $menuItem['submenu'],
                    function: 'getMenuStructure'
                );

            }

            return $menuStructure;
        }

        /**
         * getMenu - Return a specific menu structure
         *
         * @access public
         * @param  string $menuType Menu type to return
         * @return array  Array of menu structrue
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

            //If menu structure cannot be found, don't return anything
            if(! isset($this->menuStructures[$menuType])) {
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

            if (session()->exists("usersettings.submenuToggle") === false || is_array(session("usersettings.submenuToggle")) === false) {
                session(["usersettings.submenuToggle" => array()]);
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
                        //TO DO: Check if menu is enabled, e.g. `$moduleManagerRepo->isModuleEnabled($element['module'])`
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
                            $submenuState = session("usersettings.submenuToggle." . $element['id']) ?? $element['visual'];
                            session(["usersettings.submenuToggle." . $element['id'] => $submenuState]);
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
                                    die("Cannot proceed due to invalid submenu element: '" . $subelement['type'] . "'");
                            }
                        }

                        break;

                    default:
                        die("Cannot proceed due to invalid menu element: '" . $element['type'] . "'");
                }
            }

            //TODO: Add menu filter here!

            return $menuStructure;
        }

        /**
         * @param $element
         * @param $structure
         * @return void
         */
        public function processMenuItem($element, &$structure): void
        {

            //ModuleManager Check
            if (false) {
                $structure['type'] = 'disabled';
                return;
            }

            // Update security
            if (isset($element['role'])) {
                $accessGranted = AuthService::userIsAtLeast($element['role'], true);

                if (!$accessGranted) {
                    $structure['type'] = 'disabled';
                }
            }

            if (isset($element['hrefFunction'])) {
                if (method_exists($this, $element['hrefFunction'])) {
                    $structure['href'] = $this->{$element['hrefFunction']}();
                }
            }

            return;
        }

        /**
         * @return array|mixed|string|string[]
         */
        public function getTicketMenu(): mixed
        {

            $ticketService = $this->ticketsService;

            //Removing base URL from here since it is being added in the menu for loop in the template
            $base_url = !empty($this->config->appUrl) ? $this->config->appUrl : BASE_URL;
            return str_replace($base_url, '', $ticketService->getLastTicketViewUrl());
        }

        public function getTimelineMenu(): mixed
        {

            $ticketService = $this->ticketsService;

            //Removing base URL from here since it is being added in the menu for loop in the template
            $base_url = !empty($this->config->appUrl) ? $this->config->appUrl : BASE_URL;
            return str_replace($base_url, '', $ticketService->getLastTimelineViewUrl());
        }



        /**
         * @return string
         */
        public function getIdeaMenu(): string
        {
            $url = "/ideas/showBoards";
            if (session()->exists("lastIdeaView")) {
                if (session("lastIdeaView") == 'kanban') {
                    $url = "/ideas/advancedBoards";
                }
            }

            return $url;
        }

        public function getSectionMenuType($currentRoute, $default = "default")
        {
            $sections = [
                "dashboard.home" => "personal",
                "projects.showMy" => "personal",
                "timesheets.showMy" => "personal",
                "calendar.showMyCalendar" => "personal",
                "calendar.showMyList" => "personal",
                "tickets.roadmapAll" => "personal",
                "notes.showNotes" => "personal",
                "notes.showNotesList" => "personal",
                "tickets.showAllMilestonesOverview" => "personal",
                "users.editOwn" => "personal",
                "setting.editCompanySettings" => "company",
                "timesheets.showAll" => "company",
                "projects.showAll" => "company",
                "clients.showAll" => "company",
                "clients.newClient" => "company",
                "clients.showClient" => "company",
                "users.showAll" => "company",
                "plugins.show" => "company",
                "plugins.marketplace" => "company",
                "plugins.myapps" => "company",
                "connector.show" => "company",
                "billing.subscriptions" => "company",
                "llamadorian.statusCollector" => "personal",
            ];

            $sections = self::dispatch_filter('menuSections', $sections, array("currentRoute" => $currentRoute, "default" => $default));


            if (isset($sections[$currentRoute])) {
                return $sections[$currentRoute];
            } else {
                return $default;
            }
        }
    }

}
