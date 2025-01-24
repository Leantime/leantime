<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Timesheets\Services\Timesheets;
use Leantime\Domain\Projects\Services\Projects;
use Symfony\Component\HttpFoundation\Response;
use Leantime\Core\Support\FromFormat;



class ShowKanban extends HtmxController
{
    protected static string $view = 'tickets::components.kanban-board';

    private Tickets $ticketService;
    private Timesheets $timesheetService;
    private Projects $projectService;

    /**
     * Controller constructor
     *
     */
    public function init(Tickets $ticketService, Projects $projectService, Timesheets $timesheetService): void
    {
        $this->ticketService = $ticketService;
        $this->projectService = $projectService;
        $this->timesheetService = $timesheetService;
    }


    public function get($params): void
    {
        // $allTickets = $this->ticketService->getAll($params);

        $searchCriteria = $this->ticketService->prepareTicketSearchArray($params);
        $searchCriteria['orderBy'] = 'kanbansort';

        $allTickets = $this->ticketService->getAllGrouped($searchCriteria);

        $ticketTypeIcons = $this->ticketService->getTypeIcons();
        $priorities = $this->ticketService->getPriorityLabels();
        $efforts = $this->ticketService->getEffortLabels();
        $milestones = $this->ticketService->getAllMilestones(['sprint' => '', 'type' => 'milestone', 'currentProject' => session('currentProject')]);
        $users = $this->projectService->getUsersAssignedToProject(session('currentProject'));
        $onTheClock = $this->timesheetService->isClocked(session('userdata.id'));

        $this->tpl->assign('allKanbanColumns', $this->ticketService->getKanbanColumns());
        $this->tpl->assign('onTheClock', $onTheClock);
        $this->tpl->assign("efforts", $efforts);
        $this->tpl->assign("milestones", $milestones);
        $this->tpl->assign("users", $users);
        $this->tpl->assign("allTicketGroups", $allTickets);
        $this->tpl->assign("ticketTypeIcons", $ticketTypeIcons);
        $this->tpl->assign("priorities", $priorities);
        $this->tpl->assign("searchCriteria", $searchCriteria);
    }

    public function post($params): Response
    {
        $result = $this->ticketService->quickAddTicket($params);
        return response()->json(['success' => $result]);
    }
}
