<?php

namespace Leantime\Domain\Tickets\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Sprints\Services\Sprints as SprintService;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class ShowAllMilestones extends Controller
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
            session(["lastMilestoneView" => "milestonetable"]);
            session(["lastFilterdMilestoneView" => CURRENT_URL]);
        }

        /**
         * @param $params
         * @return Response
         * @throws \Exception
         */
        public function get($params): Response
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
            $this->tpl->assign("showTasks", $params["showTasks"] ?? "false");

            $template_assignments = $this->ticketService->getTicketTemplateAssignments($params);
            array_map([$this->tpl, 'assign'], array_keys($template_assignments), array_values($template_assignments));

            return $this->tpl->display('tickets.showAllMilestones');
        }
    }
}
