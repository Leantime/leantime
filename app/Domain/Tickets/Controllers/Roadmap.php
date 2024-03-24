<?php

namespace Leantime\Domain\Tickets\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Sprints\Services\Sprints as SprintService;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class Roadmap extends Controller
    {
        private ProjectRepository $projectsRepo;
        private SprintService $sprintService;
        private TicketService $ticketService;

        /**
         * init - initialize private variables
         *
         * @access public
         *
         */
        public function init(
            ProjectRepository $projectsRepo,
            SprintService $sprintService,
            TicketService $ticketService
        ) {
            $this->projectsRepo = $projectsRepo;
            $this->sprintService = $sprintService;
            $this->ticketService = $ticketService;

            $_SESSION['lastPage'] = CURRENT_URL;
            $_SESSION['lastMilestoneView'] = "timeline";
            $_SESSION['lastFilterdMilestonesView'] = CURRENT_URL;
        }

        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {

            if (isset($params["type"]) === false) {
                $params["type"] = 'milestone';
            }

            if (isset($params["showTasks"]) === true) {
                $params["type"] = '';
                $params["excludeType"] = '';
            }

            //Sets the filter module to show a quick toggle for task types
            $this->tpl->assign("enableTaskTypeToggle", true);
            $this->tpl->assign("showTasks", $params["showTasks"] ?? 'false');

            $template_assignments = $this->ticketService->getTicketTemplateAssignments($params);



            array_map([$this->tpl, 'assign'], array_keys($template_assignments), array_values($template_assignments));

            $allProjectMilestones = $this->ticketService->getAllMilestones($template_assignments['searchCriteria']);
            $allProjectMilestones = $this->ticketService->getBulkMilestoneProgress($allProjectMilestones);

            $this->tpl->assign('timelineTasks', $allProjectMilestones);

            return $this->tpl->display('tickets.roadmap');
        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {

            $template_assignments = $this->ticketService->getTicketTemplateAssignments($params);
            array_map([$this->tpl, 'assign'], array_keys($template_assignments), array_values($template_assignments));

            $allProjectMilestones = $this->ticketService->getAllMilestones($template_assignments['searchCriteria']);

            $this->tpl->assign('timelineTasks', $allProjectMilestones);

            return $this->tpl->display('tickets.roadmap');
        }

        /**
         * put - handle put requests
         *
         * @access public
         *
         */
        public function put($params)
        {
        }

        /**
         * delete - handle delete requests
         *
         * @access public
         *
         */
        public function delete($params)
        {
        }
    }

}
