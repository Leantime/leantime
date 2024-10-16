<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Timesheets\Services\Timesheets;

class TicketCard extends HtmxController
{
    protected static string $view = 'tickets::components.ticket-card';

    private Tickets $ticketService;

    /**
     * Controller constructor
     *
     * @param  Timesheets  $timesheetService
     */
    public function init(Tickets $ticketService): void
    {
        $this->ticketService = $ticketService;
    }

    public function save(): void
    {

        $postParams = $_POST;
        $id = $postParams['id'];

        $ticket = $this->ticketService->getTicket($id);

        $values = $ticket;

        //Until we have everything as objects we'll need to use arrays
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
        $ticket = (array)$this->ticketService->getTicket($ticketId);
        $efforts = $this->ticketService->getEffortLabels();
        $statusLabels = $this->ticketService->getStatusLabels();
        $milestones = $this->ticketService->getAllMilestones(['sprint' => '', 'type' => 'milestone', 'currentProject' => session('currentProject')]);

        $this->tpl->assign('onTheClock', $this->timesheetService->isClocked(session('userdata.id')));
        $this->tpl->assign("ticket", $ticket);
        $this->tpl->assign("effort", $efforts);
        $this->tpl->assign("statusLabels", $statusLabels);
        $this->tpl->assign("milestones", $milestones);
    }
}
