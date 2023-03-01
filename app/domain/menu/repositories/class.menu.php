<?php

/**
 * menu class - Menu definitions
 */

namespace leantime\domain\repositories {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models\auth\roles;

    class menu
    {
        // Default menu
        public const DEFAULT_MENU = 'default';

        // Menu structures
        private array $menuStructures = [
            'default' => [
                5 => ['type' => 'item', 'module' => 'dashboard', 'title' => 'menu.dashboard', 'href' => '/dashboard/show', 'active' => ['show']],

                10 => ['type' => 'submenu', 'id' => 'materialize', 'title' => 'menu.make', 'visual' => 'open',
                    'submenu' => [
                         15 => ['type' => 'item', 'module' => 'tickets', 'title' => 'menu.todos', 'href' => '', 'hrefFunction' => 'getTicketMenu', 'active' => ['showKanban', 'showAll', 'showTicket', 'showList']],
                         60 => ['type' => 'item', 'module' => 'wiki', 'title' => 'menu.wiki', 'href' => '/wiki/show'],

                        20 => ['type' => 'item', 'module' => 'tickets', 'title' => 'menu.milestones', 'href' => '/tickets/roadmap', 'active' => ['roadmap']],

                        40 => ['type' => 'item', 'module' => 'goalcanvas', 'title' => 'menu.goals', 'href' => '/goalcanvas/showCanvas', 'active' => ['showCanvas']],

                    ]],

                30 => ['type' => 'submenu', 'id' => 'understand', 'title' => 'menu.think', 'visual' => 'open',

                    'submenu' => [

                        30 => ['type' => 'item', 'module' => 'ideas', 'title' => 'menu.ideas', 'href' => '', 'hrefFunction' => 'getIdeaMenu', 'active' => ['showBoards', 'advancedBoards']],
                        50 => ['type' => 'item', 'module' => 'strategy', 'title' => 'menu.blueprints', 'href' => '/strategy/showBoards', 'active' => ['showBoards']],



                        70 => ['type' => 'item', 'module' => 'retroscanvas', 'title' => 'menu.retroscanvas', 'href' => '/retroscanvas/showCanvas'],
                        80 => ['type' => 'item', 'module' => 'reports', 'title' => 'menu.reports', 'href' => '/reports/show', 'role' => 'editor'],
                ]],
            ],
            //Display all menu items
            'full_menu' => [
                10 => ['type' => 'submenu', 'id' => 'planning', 'title' => 'menu.planning_execution', 'visual' => 'open',
                    'submenu' => [
                        11 => ['type' => 'item', 'module' => 'dashboard', 'title' => 'menu.dashboard', 'href' => '/dashboard/show', 'active' => ['show']],
                        21 => ['type' => 'item', 'module' => 'tickets', 'title' => 'menu.todos', 'href' => '', 'hrefFunction' => 'getTicketMenu', 'active' => ['showKanban', 'showAll', 'showTicket']],
                        31 => ['type' => 'item', 'module' => 'tickets', 'title' => 'menu.milestones', 'href' => '/tickets/roadmap', 'active' => ['roadmap']],
                        40 => ['type' => 'item', 'module' => 'goalcanvas', 'title' => 'menu.goals', 'href' => '/goalcanvas/showCanvas']
                    ]],

                50 => ['type' => 'submenu', 'id' => 'dts-process', 'title' => 'menu.dts.process', 'visual' => 'closed',
                    'submenu' => [
                        51 => ['type' => 'item', 'module' => 'insightscanvas', 'title' => 'menu.insightscanvas', 'href' => '/insightscanvas/showCanvas'],
                        52 => ['type' => 'item', 'module' => 'ideas', 'title' => 'menu.ideation', 'href' => '/ideas/showBoards']]],
                60 => ['type' => 'submenu', 'id' => 'dts-frameworks', 'title' => 'menu.dts.frameworks', 'visual' => 'closed',
                    'submenu' => [
                        61 => ['type' => 'header', 'title' => 'menu.dts.observe'],
                        62 => ['type' => 'item', 'module' => 'sbcanvas', 'title' => 'menu.sbcanvas', 'href' => '/sbcanvas/showCanvas'],
                        63 => ['type' => 'item', 'module' => 'riskscanvas', 'title' => 'menu.riskscanvas', 'href' => '/riskscanvas/showCanvas'],
                        64 => ['type' => 'item', 'module' => 'eacanvas', 'title' => 'menu.eacanvas', 'href' => '/eacanvas/showCanvas'],
                        65 => ['type' => 'header', 'title' => 'menu.dts.design'],
                        66 => ['type' => 'item', 'module' => 'lbmcanvas', 'title' => 'menu.lbmcanvas', 'href' => '/lbmcanvas/showCanvas'],
                        67 => ['type' => 'item', 'module' => 'dbmcanvas', 'title' => 'menu.dbmcanvas', 'href' => '/dbmcanvas/showCanvas'],
                        68 => ['type' => 'item', 'module' => 'cpcanvas', 'title' => 'menu.cpcanvas', 'href' => '/cpcanvas/showCanvas'],
                        69 => ['type' => 'header', 'title' => 'menu.dts.validate'],
                        70 => ['type' => 'item', 'module' => 'sqcanvas', 'title' => 'menu.sqcanvas', 'href' => '/sqcanvas/showCanvas'],
                        71 => ['type' => 'item', 'module' => 'smcanvas', 'title' => 'menu.smcanvas', 'href' => '/smcanvas/showCanvas']]],
                80 => ['type' => 'submenu', 'id' => 'dts-admin', 'title' => 'menu.dts.admin', 'visual' => 'open',
                    'submenu' => [
                        81 => ['type' => 'item', 'module' => 'wiki', 'title' => 'menu.wiki', 'href' => '/wiki/show'],
                        82 => ['type' => 'item', 'module' => 'retroscanvas', 'title' => 'menu.retroscanvas', 'href' => '/retroscanvas/showCanvas'],
                        83 => ['type' => 'item', 'module' => 'reports', 'title' => 'menu.reports', 'href' => '/reports/show', 'role' => 'editor']]]]
        ];

        public function __construct(){

            if(isset($_SESSION['submenuToggle']) === false){
                $setting = new setting();
                $_SESSION['submenuToggle'] = unserialize($setting->getSetting("usersetting.".$_SESSION['userdata']['id'].".submenuToggle"));
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

            $language = core\language::getInstance();
            $config = \leantime\core\environment::getInstance();

            if (!isset($config->enableMenuType) || (isset($config->enableMenuType) && $config->enableMenuType === false)) {
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

            $_SESSION['submenuToggle'][$submenu] = $state;
            $setting = new setting();
            $setting->saveSetting("usersetting.".$_SESSION['userdata']['id'].".submenuToggle", serialize($_SESSION['submenuToggle']));
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

            $language = core\language::getInstance();

            if (!isset($this->menuStructures[$menuType]) || empty($menuType)) {
                $menuType = self::DEFAULT_MENU;
            }

            $menuStructure = $this->menuStructures[$menuType];

            foreach ($menuStructure as $key => $element) {
                $menuStructure[$key]['title'] = $language->__($menuStructure[$key]['title']);

                switch ($element['type']) {
                    case 'header':
                        break;

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
                            $submenuState = $_SESSION['submenuToggle'][$element['id']] ?? $element['visual'];
                            $_SESSION['submenuToggle'][$element['id']] = $submenuState;
                        }
                        $menuStructure[$key]['visual'] = $submenuState;

                        // Parse submenu
                        foreach ($element['submenu'] as $subkey => $subelement) {
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

        public function processMenuItem($element, &$structure)
        {

            //ModuleManager Check
            if (false) {
                $structure['type'] = 'disabled';
                return;
            }

            // Update security
            if (isset($element['role'])) {
                $accessGranted = services\auth::userIsAtLeast($element['role'], true);

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

        public function getTicketMenu()
        {

            $ticketService = new services\tickets();

            //Removing base URL from here since it is being added in the menu for loop in the template
            $base_url = !empty($config->appUrl) ? $config->appUrl : BASE_URL;
            return str_replace($base_url, '', $ticketService->getLastTicketViewUrl());
        }

        public function getIdeaMenu()
        {
            $url = "/ideas/showBoards";
            if(isset($_SESSION['lastIdeaView'])){

                if($_SESSION['lastIdeaView'] == 'kanban'){
                    $url = "/ideas/advancedBoards";
                }

            }

            return $url;
        }
    }

}
