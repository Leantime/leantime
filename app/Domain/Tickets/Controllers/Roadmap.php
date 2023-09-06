<?php

namespace Leantime\Domain\Tickets\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Sprints\Services\Sprints as SprintService;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
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
        }

        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {

            $template_assignments = $this->ticketService->getTicketTemplateAssignments($params);
            array_map([$this->tpl, 'assign'], array_keys($template_assignments), array_values($template_assignments));

            $allProjectMilestones = $this->ticketService->getAllMilestones($template_assignments['searchCriteria']);
            $this->tpl->assign('milestones', $allProjectMilestones);

            $this->tpl->display('tickets.roadmap');
        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {

            $allProjectMilestones = $this->ticketService->getAllMilestones(["sprint" => '', "type" => "milestone", "currentProject" => $_SESSION["currentProject"]]);
            $this->tpl->assign('milestones', $allProjectMilestones);

            $this->tpl->display('tickets.roadmap');
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
