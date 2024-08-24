<?php

namespace Leantime\Domain\Tickets\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Sprints\Services\Sprints as SprintService;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class ShowAll extends Controller
    {
        private ProjectService $projectService;
        private TicketService $ticketService;
        private SprintService $sprintService;
        private TimesheetService $timesheetService;

        /**
         * @param ProjectService   $projectService
         * @param TicketService    $ticketService
         * @param SprintService    $sprintService
         * @param TimesheetService $timesheetService
         * @return void
         */
        public function init(
            ProjectService $projectService,
            TicketService $ticketService,
            SprintService $sprintService,
            TimesheetService $timesheetService
        ): void {

            $this->projectService = $projectService;
            $this->ticketService = $ticketService;
            $this->sprintService = $sprintService;
            $this->timesheetService = $timesheetService;

            session(["lastPage" => CURRENT_URL]);
            session(["lastTicketView" => "table"]);
            session(["lastFilterdTicketTableView" => CURRENT_URL]);

            if (!session()->exists("currentProjectName")) {
                Frontcontroller::redirect(BASE_URL . "/");
            }
        }

        /**
         * @param $params
         * @return Response
         * @throws \Exception
         */
        public function get($params): Response
        {
            $template_assignments = $this->ticketService->getTicketTemplateAssignments($params);
            array_map([$this->tpl, 'assign'], array_keys($template_assignments), array_values($template_assignments));

            return $this->tpl->display('tickets.showAll');
        }
    }
}
