<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Timesheets\Services\Timesheets;

class TicketCard extends HtmxController
{
    protected static string $view = 'tickets::partials.ticketCard';

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

        $id = (int) ($params['id']);
        $ticket = $this->ticketService->getTicket($id);

    }
}
