<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Timesheets\Services\Timesheets;
use Illuminate\Support\Facades\Cache;
use Leantime\Domain\Projects\Services\Projects as ProjectService;

class TicketCard extends HtmxController
{
    protected static string $view = 'tickets::components.cards.ticket-card';

    private Tickets $ticketService;
    private Timesheets $timesheetService;
    private ProjectService $projectService;

    public function init(Tickets $ticketService, Timesheets $timesheetService, ProjectService $projectService): void
    {
        $this->ticketService = $ticketService;
        $this->timesheetService = $timesheetService;
        $this->projectService = $projectService;
    }

    public function save(): void
    {

        $postParams = $_POST;
        $id = $postParams['id'];

        $ticket = $this->ticketService->getTicket($id);

        $values = $ticket;

        // Until we have everything as objects we'll need to use arrays
        $this->tpl->assign('row', (array) $ticket);
        $this->tpl->assign('statusLabels', $this->ticketService->getStatusLabels());
        $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
        $this->tpl->assign('priorities', $this->ticketService->getPriorityLabels());

        $allProjectMilestones = $this->ticketService->getAllMilestones([
            'sprint' => '',
            'type' => 'milestone',
            'currentProject' => session('currentProject'),
        ]);
        $this->tpl->assign('milestones', $allProjectMilestones);
    }

    public function get($params): void
    {
        $ticketId = (int) ($params['id']);
        $ticket = (array) $this->ticketService->getTicket($ticketId);


        $efforts = Cache::remember('efforts', 3600, fn() => $this->ticketService->getEffortLabels());
        $priorities = Cache::remember('priorities', 3600, fn() => $this->ticketService->getPriorityLabels());
        $statusLabels = Cache::remember('statusLabels', 3600, fn() => $this->ticketService->getStatusLabels());
        $milestones = Cache::remember('milestones', 3600, fn() => $this->ticketService->getAllMilestones([
            'sprint' => '',
            'type' => 'milestone',
            'currentProject' => session('currentProject'),
        ]));

        $users = Cache::remember('users', 3600, fn() => $this->projectService->getUsersAssignedToProject(session('currentProject')));

        $this->tpl->assign('onTheClock', $this->timesheetService->isClocked(session('userdata.id')));
        $this->tpl->assign('ticket', $ticket);
        $this->tpl->assign('efforts', $efforts);
        $this->tpl->assign('priorities', $priorities);
        $this->tpl->assign('statusLabels', $statusLabels);
        $this->tpl->assign('milestones', $milestones);
        $this->tpl->assign('users', $users);
    }
}
