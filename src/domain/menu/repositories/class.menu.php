<?php
namespace leantime\domain\repositories {

	use leantime\domain\repositories;
	use leantime\core;
		
    class menu
    {

		public const DEFAULT_MENU = 'default';

		// Menu structures
		private array $menuStructures = [ 
            'default' => [ 11 => [ 'type' => 'item', 'module' => 'dashboard',  'title' => 'menu.dashboard',  'href' => '/dashboard/show',     'active' => [ 'show' ] ],
						   21 => [ 'type' => 'item', 'module' => 'tickets',    'title' => 'menu.todos',                                       'active' => [ 'showKanban', 'showAll', 'showTicket' ] ],
						   31 => [ 'type' => 'item', 'module' => 'tickets',    'title' => 'menu.milestones', 'href' => '/tickets/roadmap', '   active' => [ 'roadmap' ] ],
						   41 => [ 'type' => 'item', 'module' => 'timesheets', 'title' => 'menu.timesheets', 'href' => '/timesheets/showAll', 'active' => [ 'showAll' ] ],
						   50 => [ 'type' => 'section', 'id' => 'default-standard', 'title' => 'menu.default.standard', 'visual' => 'open',
								   'submenu' => [
									   51 => [ 'type' => 'item', 'module' => 'insightscanvas', 'title' => 'menu.insights',   'href' => '/insightscanvas/showCanvas' ],
									   52 => [ 'type' => 'item', 'module' => 'ideas',          'title' => 'menu.ideas',      'href' => '/ideas/showBoards' ],
									   53 => [ 'type' => 'item', 'module' => 'swotcanvas',     'title' => 'menu.swotcanvas', 'href' => '/swotcanvas/showCanvas' ],
									   55 => [ 'type' => 'item', 'module' => 'leancanvas',     'title' => 'menu.leancanvas', 'href' => '/leancanvas/showCanvas' ],
									   56 => [ 'type' => 'item', 'module' => 'lbmcanvas',   'title' => 'menu.lbmcanvas',   'href' => '/lbmcanvas/showCanvas' ],
									   57 => [ 'type' => 'item', 'module' => 'obmcanvas',      'title' => 'menu.obmcanvas',  'href' => '/obmcanvas/showCanvas' ] ] ],
						   60  => [ 'type' => 'section', 'id' => 'default-advanced', 'title' => 'menu.default.advanced', 'visual' => 'closed',
									'submenu' => [
									   61 => [ 'type' => 'item', 'module' => 'riskscanvas', 'title' => 'menu.riskscanvas', 'href' => '/riskscanvas/showCanvas' ],
									   62 => [ 'type' => 'item', 'module' => 'emcanvas',    'title' => 'menu.emcanvas',    'href' => '/emcanvas/showCanvas' ],
									   63 => [ 'type' => 'item', 'module' => 'eacanvas',    'title' => 'menu.eacanvas',    'href' => '/eqcanvas/showCanvas' ],
									   64 => [ 'type' => 'item', 'module' => 'dbmcanvas',   'title' => 'menu.dbmcanvas',   'href' => '/dbmcanvas/showCanvas' ],
									   65 => [ 'type' => 'item', 'module' => 'cpcanvas',    'title' => 'menu.cpcanvas',    'href' => '/cpcanvas/showCanvas' ],
									   66 => [ 'type' => 'item', 'module' => 'sqcanvas',    'title' => 'menu.sqcanvas',    'href' => '/sqcanvas/showCanvas' ],
									   67 => [ 'type' => 'item', 'module' => 'smcanvas',    'title' => 'menu.smcanvas',    'href' => '/smcanvas/showCanvas' ] ] ],
						   71 => [ 'type' => 'item', 'module' => 'wiki','title' => 'menu.documents', 'href' => '/wiki/show' ],
						   72 => [ 'type' => 'item', 'module' => 'retrospectives','title' => 'menu.retrospectives', 'href' => '/retrospectives/showBoards', 'active' => [ 'showBoards' ] ],
						   73 => [ 'type' => 'item', 'module' => 'reports','title' => 'menu.reports', 'href' => '/reports/show', 'role' => 'editor' ] ],
	        'dts' => [  11 => [ 'type' => 'item', 'module' => 'dashboard',  'title' => 'menu.dashboard',  'href' => '/dashboard/show',     'active' => [ 'show' ] ],
						21 => [ 'type' => 'item', 'module' => 'tickets',    'title' => 'menu.todos',                                       'active' => [ 'showKanban', 'showAll', 'showTicket' ] ],
						31 => [ 'type' => 'item', 'module' => 'tickets',    'title' => 'menu.milestones', 'href' => '/tickets/roadmap',    'active' => [ 'roadmap' ] ],
						41 => [ 'type' => 'item', 'module' => 'timesheets', 'title' => 'menu.timesheets', 'href' => '/timesheets/showAll', 'active' => [ 'showAll' ] ],
						50 => [ 'type' => 'section', 'id' => 'dts-process', 'title' => 'menu.dts.process', 'visual' => 'open',
							   'submenu' => [
							       51 => [ 'type' => 'item',    'module' => 'insightscanvas', 'title' => 'menu.insightscanvas', 'href' => '/insightscanvas/showCanvas' ],
								   52 => [ 'type' => 'item',    'module' => 'ideas',          'title' => 'menu.ideation',       'href' => '/ideas/showBoards' ] ] ],
						60  => [ 'type' => 'section', 'id' => 'dts-frameworks', 'title' => 'menu.dts.frameworks', 'visual' => 'open',
								'submenu' => [
								    61 => [ 'type' => 'item', 'module' => 'sbcanvas',    'title' => 'menu.sbcanvas',    'href' => '/sbcanvas/showCanvas' ],
									62 => [ 'type' => 'item', 'module' => 'riskscanvas', 'title' => 'menu.riskscanvas', 'href' => '/riskscanvas/showCanvas' ],
									63 => [ 'type' => 'item', 'module' => 'eacanvas',    'title' => 'menu.eacanvas',    'href' => '/eqcanvas/showCanvas' ],
									64 => [ 'type' => 'item', 'module' => 'lbmcanvas',   'title' => 'menu.lbmcanvas',   'href' => '/lbmcanvas/showCanvas' ],
									65 => [ 'type' => 'item', 'module' => 'dbmcanvas',   'title' => 'menu.dbmcanvas',   'href' => '/dbmcanvas/showCanvas' ],
									66 => [ 'type' => 'item', 'module' => 'sqcanvas',    'title' => 'menu.sqcanvas',    'href' => '/sqcanvas/showCanvas' ],
									67 => [ 'type' => 'item', 'module' => 'cpcanvas',    'title' => 'menu.cpcanvas',    'href' => '/cpcanvas/showCanvas' ],
									68 => [ 'type' => 'item',  'module' => 'smcanvas',    'title' => 'menu.smcanvas',    'href' => '/smcanvas/showCanvas' ] ] ],
					   70 => [ 'type' => 'section', 'id' => 'dts-admin', 'title' => 'menu.dts.admin', 'visual' => 'open', 
							   'submenu' => [
							   71 => [ 'type' => 'item', 'module' => 'wiki','title' => 'menu.documents', 'href' => '/wiki/show' ],
							   72 => [ 'type' => 'item', 'module' => 'retrospectives','title' => 'menu.retrospectives', 'href' => '/retrospectives/showBoards', 'active' => [ 'showBoards' ] ],
							   73 => [ 'type' => 'item', 'module' => 'reports','title' => 'menu.reports', 'href' => '/reports/show', 'role' => 'editor' ] ] ] ],
	        'lean' => [  11 => [ 'type' => 'item', 'module' => 'dashboard',  'title' => 'menu.dashboard',  'href' => '/dashboard/show',  'active' => [ 'show' ] ],
						 12 => [ 'type' => 'item', 'module' => 'tickets',    'title' => 'menu.todos',                                    'active' => [ 'showKanban', 'showAll', 'showTicket' ] ],
						 13 => [ 'type' => 'item', 'module' => 'tickets',    'title' => 'menu.milestones', 'href' => '/tickets/roadmap', 'active' => [ 'roadmap' ] ],
						 13 => [ 'type' => 'item', 'module' => 'timesheets', 'title' => 'menu.timesheets', 'href' => '/timesheets/showAll', 'active' => [ 'showAll' ] ],
						 14 => [ 'type' => 'item', 'module' => 'ideas',     'title' => 'menu.ideas',    'href' => '/ideas/showBoards' ],
						 15 => [ 'type' => 'item', 'module' => 'leancanvas','title' => 'menu.research', 'href' => '/leancanvas/showCanvas' ],
						 16 => [ 'type' => 'item', 'module' => 'wiki','title' => 'menu.documents', 'href' => '/wiki/show' ],
						 17 => [ 'type' => 'item', 'module' => 'retrospectives','title' => 'menu.retrospectives', 'href' => '/retrospectives/showBoards', 'active' => [ 'showBoards' ] ],
						 18 => [ 'type' => 'item', 'module' => 'reports','title' => 'menu.reports', 'href' => '/reports/show', 'role' => 'editor' ] ]
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
			$menuTypes = [];
			foreach($this->menuStructures as $key => $menu) {
				$menuTypes[$key] = $language->__("label.menu_type.$key");
			}
			return $menuTypes;
			
		}

		/**
		 * getMenu - Return a specific menu structure
		 *
		 * @access public
		 * @return array  Array of menu structrue
		 */
		public function getMenu(string $menuType): array
		{
			
			if(!isset($this->menuStructures[$menuType])) {
				$menuType = self::DEFAULT_MENU;
			}

			return $this->menuStructures[$menuType];
			
		}

    }

  }
