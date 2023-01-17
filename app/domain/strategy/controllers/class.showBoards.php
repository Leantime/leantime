<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class showBoards extends controller
    {
        private $projectService;
        private $ticketService;
        private $menuRepo;
        private $projectRepo;

        public function init()
        {

            $this->projectService = new services\projects();
            $this->ticketService = new services\tickets();
            $this->menuRepo = new repositories\menu();
            $this->projectRepo = new repositories\projects();
            $this->canvasService = new services\canvas();
        }

        public function run()
        {



            // Ideate
                //
            // Validate
                // Empathize - Is it a problem
                // Business - Do users want it
                // Feasibility - Can we build it
                //
            // Define
                // Value
                // Metrics
                //
            // Plan
                // Timeline
                //
            // Execute
                //

            //Menu
            // Dashboard
            // Todos
            // Milestones
            // Planning
              // Project Brief
              // Risks
            // Definition

            // Validation
              // Empathy Maps
              // Lean Canvas
              // Environment
            // Ideation
            // Documents
            // Reports


            //Level 1: Validate
            $level1 = [
                //Empathy Min
                array('module' => 'minempathycanvas',       'name' => 'label.minempathycanvas',  'description' => 'description.minempathycanvas', 'icon' => 'fa-solid fa-heart-circle-check',  'numberOfBoards' => ''),

                //Lean Canvas
                array('module' => 'leancanvas',     'name' => 'label.leancanvas',       'description' => 'description.leancanvas', 'icon' => 'fa-solid fa-person-circle-question', 'numberOfBoards' => ''),

            ];

            //Level 2: Define
            $level2 = [

                // Goals & KRs
                array('module' => 'goalcanvas',       'name' => 'label.goalcanvas',  'description' => 'description.goalcanvas', 'icon' => 'fa-solid fa-bullseye',  'numberOfBoards' => ''),


                //Swot Analysis
                array('module' => 'swotcanvas',     'name' => 'label.swotcanvas', 'description' => 'description.swotcanvas', 'icon' => 'fa-solid fa-dumbbell',  'numberOfBoards' => ''),

            ];

            //Level 3: Planning<i class=""></i>
            $level3 = [

                //Project Brief<i class=""></i>
                array('module' => 'sbcanvas',       'name' => 'label.sbcanvas',  'description' => 'description.sbcanvas',           'icon' => 'fa-solid fa-briefcase',  'numberOfBoards' => ''),

                //Risks
                array('module' => 'riskscanvas',    'name' => 'label.riskscanvas',  'description' => 'description.riskscanvas',        'icon' => 'fa-solid fa-triangle-exclamation',  'numberOfBoards' => ''),

            ];

            //Everything else
            $others = [
                //Environment
                array('module' => 'eacanvas',       'name' => 'label.eacanvas', 'description' => 'description.eacanvas', 'icon' => 'fa-solid fa-seedling',  'numberOfBoards' => ''),

                //Lightweight Business Model<i class=""></i>
                array('module' => 'lbmcanvas',      'name' => 'label.lbmcanvas', 'description' => 'description.lbmcanvas', 'icon' => 'fa-solid fa-building',  'numberOfBoards' => ''),

                //Detailed Business Model<i class=""></i>
                array('module' => 'dbmcanvas',      'name' => 'label.dbmcanvas', 'description' => 'description.dbmcanvas', 'icon' => 'fa-solid fa-city',  'numberOfBoards' => ''),

                //Strategy Questions
                array('module' => 'sqcanvas',       'name' => 'label.sqcanvas', 'description' => 'description.sqcanvas', 'icon' => 'fa fa-chess',  'numberOfBoards' => ''),

                //Ethnographics<i class=""></i>
                array('module' => 'insightscanvas', 'name' => 'label.insightscanvas', 'description' => 'description.insightscanvas',      'icon' => 'fa-solid fa-arrows-down-to-people',  'numberOfBoards' => ''),

                //Competitive Canvas (Jobs to be done V2)<i class=""></i>
                array('module' => 'cpcanvas',       'name' => 'label.cpcanvas', 'description' => 'description.cpcanvas', 'icon' => 'fa-solid fa-list-check',  'numberOfBoards' => ''),

                //Strategy Messaging / Positioning<i class=""></i>
                array('module' => 'smcanvas',       'name' => 'label.smcanvas', 'description' => 'description.smcanvas', 'icon' => 'fa-solid fa-comments-dollar',  'numberOfBoards' => ''),

                //Full empathy Map<i class=""></i>
                array('module' => 'emcanvas',       'name' => 'label.emcanvas', 'description' => 'description.emcanvas', 'icon' => 'fa-solid fa-hand-holding-heart',  'numberOfBoards' => ''),

            ];

            $boards = array("emcanvas", "smcanvas", "cpcanvas", "insightscanvas",
                "sqcanvas", "dbmcanvas", "lbmcanvas", "eacanvas", "riskscanvas", "sbcanvas",
                "swotcanvas", "goalcanvas", "leancanvas", "minempathycanvas");


            $recentlyUpdatedCanvas = $this->canvasService->getLastUpdatedCanvas($_SESSION['currentProject'], $boards);
            $canvasProgress = $this->canvasService->getBoardProgress($_SESSION['currentProject'], $boards);

            $this->tpl->assign('recentlyUpdatedCanvas', $recentlyUpdatedCanvas);
            $this->tpl->assign('canvasProgress', $canvasProgress);

            $this->tpl->assign('level1Boards', $level1);
            $this->tpl->assign('level2Boards', $level2);
            $this->tpl->assign('level3Boards', $level3);
            $this->tpl->assign('otherBoards', $others);

            $this->tpl->display('strategy.showBoards');
        }
    }
}
