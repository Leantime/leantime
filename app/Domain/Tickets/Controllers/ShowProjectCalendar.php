<?php

/**
 * showAll Class - show My Calender
 *
 */

namespace Leantime\Domain\Tickets\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Sprints\Services\Sprints as SprintService;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;

    /**
     *
     */
    class ShowProjectCalendar extends Controller
    {
        private CalendarRepository $calendarRepo;
        private ProjectRepository $projectsRepo;
        private SprintService $sprintService;
        private TicketService $ticketService;
        private ProjectService $projectService;

        /**
         * init - initialize private variables
         */
        public function init(
            ProjectService $projectService,
            CalendarRepository $calendarRepo,
            ProjectRepository $projectsRepo,
            SprintService $sprintService,
            TicketService $ticketService
        ) {
            $this->projectService = $projectService;
            $this->calendarRepo = $calendarRepo;
            $this->projectsRepo = $projectsRepo;
            $this->sprintService = $sprintService;
            $this->ticketService = $ticketService;

            session(["lastPage" => CURRENT_URL]);
            session(["lastMilestoneView" => "calendar"]);

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

            return $this->tpl->display('tickets.calendar');
        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {
            $allProjectMilestones = $this->ticketService->getAllMilestones(["sprint" => '', "type" => "milestone", "currentProject" => session("currentProject")]);
            $this->tpl->assign('milestones', $allProjectMilestones);
            return $this->tpl->display('tickets.roadmap');
        }
    }
}
