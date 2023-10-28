<?php

namespace Leantime\Domain\Tickets\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Leantime\Domain\Sprints\Services\Sprints as SprintService;
    use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;

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

            $_SESSION['lastPage'] = CURRENT_URL;
            $_SESSION['lastTicketView'] = "table";
            $_SESSION['lastFilterdTicketTableView'] = CURRENT_URL;

            if(!isset($_SESSION['currentProjectName'])){
               $this->tpl->redirect(BASE_URL."/");
            }
        }

        /**
         * @param $params
         * @return void
         * @throws \Exception
         */
        public function get($params): void
        {

            $template_assignments = $this->ticketService->getTicketTemplateAssignments($params);
            array_map([$this->tpl, 'assign'], array_keys($template_assignments), array_values($template_assignments));

            $this->tpl->display('tickets.showAll');
        }
    }

}
