<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Timesheets\Services\Timesheets;
use Leantime\Domain\Projects\Services\Projects;


class TicketColumn extends HtmxController
{
    protected static string $view = 'tickets::components.ticket-column';

    private Tickets $ticketService;
    private Projects $projectService;
    private Timesheets $timesheetService;
    /**
     * Controller constructor
     *
     * @param  Timesheets  $timesheetService
     */
    public function init(Tickets $ticketService, Projects $projectService, Timesheets $timesheetService): void
    {
        $this->ticketService = $ticketService;
        $this->projectService = $projectService;
        $this->timesheetService = $timesheetService;
    }

    public function get($params): void
    {
        // $ticketId = (int) ($params['id']);
        $statusKey = (int) ($params['status']);
        $allTickets = $this->ticketService->getAll($params);
        $ticketTypeIcons = $this->ticketService->getTypeIcons();
        $priorities = $this->ticketService->getPriorityLabels();
        $efforts = $this->ticketService->getEffortLabels();
        $milestones = $this->ticketService->getAllMilestones(['sprint' => '', 'type' => 'milestone', 'currentProject' => session('currentProject')]);
        $users = $this->projectService->getUsersAssignedToProject(session('currentProject'));
        $onTheClock = $this->timesheetService->isClocked(session('userdata.id'));

        $this->tpl->assign('onTheClock', $onTheClock);
        $this->tpl->assign("efforts", $efforts);
        $this->tpl->assign("milestones", $milestones);
        $this->tpl->assign("users", $users);
        $this->tpl->assign("allTickets", $allTickets);
        $this->tpl->assign("ticketTypeIcons", $ticketTypeIcons);
        $this->tpl->assign("priorities", $priorities);
        $this->tpl->assign("statusKey", $statusKey);

    }
}
