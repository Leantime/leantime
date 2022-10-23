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
                11 => [ 'type' => 'item', 'module' => 'dashboard',  'title' => 'menu.dashboard',  'href' => '/dashboard/show',     'active' => [ 'show' ] ],
                21 => [ 'type' => 'item', 'module' => 'tickets',    'title' => 'menu.todos',                                       'active' => [ 'showKanban', 'showAll', 'showTicket' ] ],
                31 => [ 'type' => 'item', 'module' => 'tickets',    'title' => 'menu.milestones', 'href' => '/tickets/roadmap', '   active' => [ 'roadmap' ] ],
                41 => [ 'type' => 'item', 'module' => 'timesheets', 'title' => 'menu.timesheets', 'href' => '/timesheets/showAll', 'active' => [ 'showAll' ] ],
                51 => [ 'type' => 'header', 'title' => 'menu.default.define' ],
                52 => [ 'type' => 'item', 'module' => 'swotcanvas',     'title' => 'menu.swotcanvas', 'href' => '/swotcanvas/showCanvas' ],
                53 => [ 'type' => 'item', 'module' => 'insightscanvas', 'title' => 'menu.insights',   'href' => '/insightscanvas/showCanvas' ],
                54 => [ 'type' => 'header', 'title' => 'menu.default.ideate' ],
                55 => [ 'type' => 'item', 'module' => 'ideas',           'title' => 'menu.ideas',      'href' => '/ideas/showBoards' ],
                56 => [ 'type' => 'item', 'module' => 'leancanvas',      'title' => 'menu.leancanvas', 'href' => '/leancanvas/showCanvas' ],
                57 => [ 'type' => 'item', 'module' => 'lbmcanvas',       'title' => 'menu.lbmcanvas',   'href' => '/lbmcanvas/showCanvas' ],
                58 => [ 'type' => 'item', 'module' => 'obmcanvas',       'title' => 'menu.obmcanvas',  'href' => '/obmcanvas/showCanvas' ],
                60 => [ 'type' => 'submenu', 'id' => 'default-advanced', 'title' => 'menu.default.advanced', 'visual' => 'closed',
                        'submenu' => [
                            61 => [ 'type' => 'header', 'title' => 'menu.default.define' ],
                            62 => [ 'type' => 'item', 'module' => 'riskscanvas', 'title' => 'menu.riskscanvas', 'href' => '/riskscanvas/showCanvas' ],
                            63 => [ 'type' => 'item', 'module' => 'emcanvas',    'title' => 'menu.emcanvas',    'href' => '/emcanvas/showCanvas' ],
                            64 => [ 'type' => 'item', 'module' => 'eacanvas',    'title' => 'menu.eacanvas',    'href' => '/eacanvas/showCanvas' ],
                            65 => [ 'type' => 'header', 'title' => 'menu.default.ideate' ],
                            66 => [ 'type' => 'item', 'module' => 'dbmcanvas',   'title' => 'menu.dbmcanvas',   'href' => '/dbmcanvas/showCanvas' ],
                            67 => [ 'type' => 'item', 'module' => 'cpcanvas',    'title' => 'menu.cpcanvas',    'href' => '/cpcanvas/showCanvas' ],
                            58 => [ 'type' => 'header', 'title' => 'menu.default.test' ],
                            69 => [ 'type' => 'item', 'module' => 'sqcanvas',    'title' => 'menu.sqcanvas',    'href' => '/sqcanvas/showCanvas' ],
                            70 => [ 'type' => 'item', 'module' => 'smcanvas',    'title' => 'menu.smcanvas',    'href' => '/smcanvas/showCanvas' ] ] ],
                71 => [ 'type' => 'header', 'title' => 'menu.default.tools' ],
                72 => [ 'type' => 'item', 'module' => 'wiki','title' => 'menu.documents', 'href' => '/wiki/show' ],
                73 => [ 'type' => 'item', 'module' => 'retroscanvas','title' => 'menu.retroscanvas', 'href' => '/retroscanvas/showCanvas' ],
                74 => [ 'type' => 'item', 'module' => 'reports','title' => 'menu.reports', 'href' => '/reports/show', 'role' => 'editor' ] ],
            'dts' => [
                11 => [ 'type' => 'item', 'module' => 'dashboard',  'title' => 'menu.dashboard',  'href' => '/dashboard/show',     'active' => [ 'show' ] ],
                21 => [ 'type' => 'item', 'module' => 'tickets',    'title' => 'menu.todos',                                       'active' => [ 'showKanban', 'showAll', 'showTicket' ] ],
                31 => [ 'type' => 'item', 'module' => 'tickets',    'title' => 'menu.milestones', 'href' => '/tickets/roadmap',    'active' => [ 'roadmap' ] ],
                41 => [ 'type' => 'item', 'module' => 'timesheets', 'title' => 'menu.timesheets', 'href' => '/timesheets/showAll', 'active' => [ 'showAll' ] ],
                50 => [ 'type' => 'submenu', 'id' => 'dts-process', 'title' => 'menu.dts.process', 'visual' => 'open',
                        'submenu' => [
                            51 => [ 'type' => 'item',    'module' => 'insightscanvas', 'title' => 'menu.insightscanvas', 'href' => '/insightscanvas/showCanvas' ],
                            52 => [ 'type' => 'item',    'module' => 'ideas',          'title' => 'menu.ideation',       'href' => '/ideas/showBoards' ] ] ],
                60  => [ 'type' => 'submenu', 'id' => 'dts-frameworks', 'title' => 'menu.dts.frameworks', 'visual' => 'open',
                         'submenu' => [
                             61 => [ 'type' => 'header', 'title' => 'menu.dts.observe' ],
                             62 => [ 'type' => 'item',   'module' => 'sbcanvas',    'title' => 'menu.sbcanvas',    'href' => '/sbcanvas/showCanvas' ],
                             63 => [ 'type' => 'item',   'module' => 'riskscanvas', 'title' => 'menu.riskscanvas', 'href' => '/riskscanvas/showCanvas' ],
                             64 => [ 'type' => 'item',   'module' => 'eacanvas',    'title' => 'menu.eacanvas',    'href' => '/eacanvas/showCanvas' ],
                             65 => [ 'type' => 'header', 'title' => 'menu.dts.design' ],
                             66 => [ 'type' => 'item',   'module' => 'lbmcanvas',   'title' => 'menu.lbmcanvas',   'href' => '/lbmcanvas/showCanvas' ],
                             67 => [ 'type' => 'item',   'module' => 'dbmcanvas',   'title' => 'menu.dbmcanvas',   'href' => '/dbmcanvas/showCanvas' ],
                             68 => [ 'type' => 'item',   'module' => 'cpcanvas',    'title' => 'menu.cpcanvas',    'href' => '/cpcanvas/showCanvas' ],
                             69 => [ 'type' => 'header', 'title' => 'menu.dts.validate' ],
                             70 => [ 'type' => 'item',   'module' => 'sqcanvas',    'title' => 'menu.sqcanvas',    'href' => '/sqcanvas/showCanvas' ],
                             71 => [ 'type' => 'item',   'module' => 'smcanvas',    'title' => 'menu.smcanvas',    'href' => '/smcanvas/showCanvas' ] ] ],
                80 => [ 'type' => 'submenu', 'id' => 'dts-admin', 'title' => 'menu.dts.admin', 'visual' => 'open', 
                        'submenu' => [
                            81 => [ 'type' => 'item', 'module' => 'wiki','title' => 'menu.documents', 'href' => '/wiki/show' ],
                            82 => [ 'type' => 'item', 'module' => 'retroscanvas','title' => 'menu.retroscanvas', 'href' => '/retroscanvas/showCanvas' ],
                            83 => [ 'type' => 'item', 'module' => 'reports','title' => 'menu.reports', 'href' => '/reports/show', 'role' => 'editor' ] ] ] ],
            'lean' => [
                11 => [ 'type' => 'item', 'module' => 'dashboard',  'title' => 'menu.dashboard',  'href' => '/dashboard/show',  'active' => [ 'show' ] ],
                12 => [ 'type' => 'item', 'module' => 'tickets',    'title' => 'menu.todos',                                    'active' => [ 'showKanban', 'showAll', 'showTicket' ] ],
                13 => [ 'type' => 'item', 'module' => 'tickets',    'title' => 'menu.milestones', 'href' => '/tickets/roadmap', 'active' => [ 'roadmap' ] ],
                14 => [ 'type' => 'item', 'module' => 'timesheets', 'title' => 'menu.timesheets', 'href' => '/timesheets/showAll', 'active' => [ 'showAll' ] ],
                15 => [ 'type' => 'item', 'module' => 'ideas',     'title' => 'menu.ideas',    'href' => '/ideas/showBoards' ],
                16 => [ 'type' => 'item', 'module' => 'leancanvas','title' => 'menu.research', 'href' => '/leancanvas/showCanvas' ],
                17 => [ 'type' => 'item', 'module' => 'wiki','title' => 'menu.documents', 'href' => '/wiki/show' ],
                18 => [ 'type' => 'item', 'module' => 'retroscanvas','title' => 'menu.retroscanvas', 'href' => '/retroscanvas/showCanvas' ],
                19 => [ 'type' => 'item', 'module' => 'reports','title' => 'menu.reports', 'href' => '/reports/show', 'role' => 'editor' ] ]
        ];
        
        /**
         * getMenuTypes - Return an array of a currently supported menu types
         *
         * @access public
         * @return array  Array of supported menu types
         */
        public function getMenuTypes(): array
        {

            $language = new core\language();
			$config = new core\config();

			if(!$config->enableMenuType) {
                
				return [ self::DEFAULT_MENU => $language->__('label.menu_type.'.self::DEFAULT_MENU) ];
                
            }
            
            $menuTypes = [];
            
            foreach($this->menuStructures as $key => $menu) {
                
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

            $language = new core\language();
            
            if(!isset($this->menuStructures[$menuType]) || empty($menuType)) {
                
                $menuType = self::DEFAULT_MENU;
                
            }
            $menuStructure = $this->menuStructures[$menuType];

            foreach($menuStructure as $key => $element) {
                
                $menuStructure[$key]['title'] = $language->__($menuStructure[$key]['title']);

                switch($element['type']) {
                case 'header':
                    break;

                case 'item':
                    // Update security
                    if(isset($element['role'])) {
                        switch($element['role']) {
                        case 'editor':
                            $accessGranted = services\auth::userIsAtLeast(roles::$editor);
                            break;
                        case 'manager':
                            $accessGranted = services\auth::userIsAtLeast(roles::$manager);
                            break;
                        default:
                            die("Cannot proceed due to invalid role: '".$element['role']."'");
                        }

                        if(!$accessGranted) {

                            $menuStructure[$key]['type'] = 'disabled';

                        }
                    }

                    // Patch link
                    if($element['module'] == 'tickets' && !isset($element['href'])) {

                        $ticketService = new services\tickets();
                        $config = new core\config();
                                
                        $menuStructure[$key]['href'] = str_replace($config->appUrl, '', $ticketService->getLastTicketViewUrl());

                    }
                    break;

                case 'submenu':
                    // Update menu toggle
                    if($element['visual'] == 'always') {

                        $menuStructure[$key]['visual'] = 'open';

                    }else{

                        $submenuState = $_SESSION['submenuToggle'][$element['id']] ?? $element['visual'];
                        $_SESSION['submenuToggle'][$element['id']] = $submenuState;

                    }
                    $menuStructure[$key]['visual'] = $submenuState;

                    // Parse submenu
                    foreach($element['submenu'] as $subkey => $subelement) {

                        $menuStructure[$key]['submenu'][$subkey]['title'] = $language->__($menuStructure[$key]['submenu'][$subkey]['title']);
                    
                        switch($subelement['type']) {
                        case 'header':
                            break;

                        case 'item':
                            // Update security
                            if(isset($subelement['role'])) {

                                switch($subelement['role']) {
                                case 'editor':
                                    $accessGranted = services\auth::userIsAtLeast(roles::$editor);
                                    break;
                                case 'manager':
                                    $accessGranted = services\auth::userIsAtLeast(roles::$manager);
                                    break;
                                default:
                                    die("Cannot proceed due to invalid role: '".$subelement['role']."'");
                                }
                                
                                if(!$accessGranted) {

                                    $menuStructure[$key]['submenu'][$subkey]['type'] = 'disabled';

                                }

                            }
                                
                            // Patch link
                            if($subelement['module'] == 'tickets' && !isset($subelement['href'])) {

                                $ticketService = new services\tickets();
                                $config = new core\config();
                                
                                $menuStructure[$key]['submenu'][$subkey]['href'] = str_replace($config->appUrl, '', $ticketService->getLastTicketViewUrl());

                            }
                            break;
                            
                        default:
                            die("Cannot proceed due to invalid submenu element: '".$subelement['type']."'");
                        }
                    }
                    break;
                    
                default:
                    die("Cannot proceed due to invalid menu element: '".$element['type']."'");
                }
            }

            return $menuStructure;
            
        }

    }

}
