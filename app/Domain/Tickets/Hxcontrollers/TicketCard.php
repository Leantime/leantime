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

    /**
     * Controller constructor
     *
     * @param Timesheets $timesheetService
     * @return void
     */
    public function init(Tickets $ticketService): void
    {
        $this->ticketService = $ticketService;
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

        $id = (int)($params['id']);
        $ticket = $this->ticketService->getTicket($id);

    }
}
