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
        private services\projects $projectService;

        /**
         * init - initialize private variables
         */
        public function init(
            services\projects $projectService,
            repositories\calendar $calendarRepo,
            repositories\projects $projectsRepo,
            services\sprints $sprintService,
            services\tickets $ticketService
        ) {
            $this->projectService = $projectService;
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

            $currentSprint = $this->sprintService->getCurrentSprintId($_SESSION['currentProject']);

            $params["orderBy"] = "date";
            $searchCriteria = $this->ticketService->prepareTicketSearchArray($params);

            $this->tpl->assign("includeTasks", $includeTasks);
            $this->tpl->assign('milestones', $allProjectMilestones);

            $this->tpl->assign('allTicketStates', $this->ticketService->getStatusLabels());
            $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
            $this->tpl->assign('priorities', $this->ticketService->getPriorityLabels());
            $this->tpl->assign('types', $this->ticketService->getTicketTypes());
            $this->tpl->assign('ticketTypeIcons', $this->ticketService->getTypeIcons());

            $this->tpl->assign('searchCriteria', $searchCriteria);
            $this->tpl->assign('numOfFilters', $this->ticketService->countSetFilters($searchCriteria));

            $this->tpl->assign('sprints', $this->sprintService->getAllSprints($_SESSION["currentProject"]));
            $this->tpl->assign('futureSprints', $this->sprintService->getAllFutureSprints($_SESSION["currentProject"]));

            $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject($_SESSION["currentProject"]));
            $this->tpl->assign('milestones', $this->ticketService->getAllMilestones($_SESSION["currentProject"]));

            $this->tpl->assign('currentSprint', $_SESSION["currentSprint"]);
            $this->tpl->assign('sprints', $this->sprintService->getAllSprints($_SESSION["currentProject"]));

            $this->tpl->assign('groupBy', $this->ticketService->getGroupByFieldOptions());
            $this->tpl->assign('newField', $this->ticketService->getNewFieldOptions());
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
