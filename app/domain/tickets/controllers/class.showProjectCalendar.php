<?php

/**
 * showAll Class - show My Calender
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;
    use leantime\domain\services;

    class showProjectCalendar extends controller
    {
        private repositories\calendar $calendarRepo;
        private repositories\projects $projectsRepo;
        private services\sprints $sprintService;
        private services\tickets $ticketService;

        /**
         * init - initialize private variables
         */
        public function init(
            repositories\calendar $calendarRepo,
            repositories\projects $projectsRepo,
            services\sprints $sprintService,
            services\tickets $ticketService
        ) {
            $this->calendarRepo = $calendarRepo;
            $this->projectsRepo = $projectsRepo;
            $this->sprintService = $sprintService;
            $this->ticketService = $ticketService;
        }

        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {

            if (isset($_SESSION["usersettings.showMilestoneTasks"]) && $_SESSION["usersettings.showMilestoneTasks"] === true) {
                $includeTasks = true;
            } else {
                $includeTasks = false;
                $_SESSION["usersettings.showMilestoneTasks"] = false;
            }

            if (isset($_GET['includeTasks']) && $_GET['includeTasks'] == "on") {
                $includeTasks = true;
                $_SESSION["usersettings.showMilestoneTasks"] = true;
            } elseif (isset($_GET['submitIncludeTasks']) && !isset($_GET['includeTasks'])) {
                $includeTasks = false;
                $_SESSION["usersettings.showMilestoneTasks"] = false;
            }

            $allProjectMilestones = $this->ticketService->getAllMilestones($_SESSION['currentProject'], false, "date", $includeTasks);

            $this->tpl->assign("includeTasks", $includeTasks);
            $this->tpl->assign('milestones', $allProjectMilestones);
            $this->tpl->display('tickets.calendar');
        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {

            $allProjectMilestones = $this->ticketService->getAllMilestones($_SESSION['currentProject']);

            $this->tpl->assign('milestones', $allProjectMilestones);
            $this->tpl->display('tickets.roadmap');
        }

    }

}
