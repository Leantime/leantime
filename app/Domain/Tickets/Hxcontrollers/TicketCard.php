<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 *
 */
class TicketCard extends HtmxController
{
    /**
     * @var string
     */
    protected static string $view = 'tickets::partials.ticketCard';

    /**
     * @var Tickets
     */
    private Tickets $ticketService;
    private Timesheets $timesheetService;

    /**
     * Controller constructor
     *
     * @param Timesheets $timesheetService
     * @return void
     */
    public function init(Tickets $ticketService, Timesheets $timesheetService): void
    {
        $this->ticketService = $ticketService;
        $this->timesheetService = $timesheetService;
    }

    /**
     * @return void
     */
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
            "sprint" => '',
            "type" => "milestone",
            "currentProject" => session("currentProject"),
        ]);

        $this->tpl->assign('milestones', $allProjectMilestones);


    }

    /**
     * @return void
     */
    public function get($params): void
    {

        $id = $params['id'] ?? $params['ticketId'] ?? null;
        $cardType = $params['type'] ?? "full";

        $ticket = $this->ticketService->getTicket($id);
        $onTheClock = $this->timesheetService->isClocked(session("userdata.id"));

        $this->tpl->assign('cardType', $cardType);
        $this->tpl->assign('onTheClock', $onTheClock);
        $this->tpl->assign('statusLabels', $this->ticketService->getStatusLabels());
        $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
        $this->tpl->assign('priorities', $this->ticketService->getPriorityLabels());

        $allProjectMilestones = $this->ticketService->getAllMilestones([
            "sprint" => '',
            "type" => "milestone",
            "currentProject" => $ticket->projectId,
        ]);
        $this->tpl->assign('milestones', $allProjectMilestones);

        $this->tpl->assign('row', (array)$ticket);

    }
}
