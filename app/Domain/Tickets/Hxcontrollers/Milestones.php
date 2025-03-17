<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Timesheets\Services\Timesheets;

class Milestones extends HtmxController
{
    protected static string $view = 'tickets::partials.milestoneCard';

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

    public function progress()
    {

        $getParams = $_GET;

        $milestone = $this->ticketService->getTicket($getParams['milestoneId']);
        $percentDone = $this->ticketService->getMilestoneProgress($getParams['milestoneId']);

        $this->tpl->assign('progressColor', $getParams['progressColor'] ?? 'default');
        $this->tpl->assign('noText', $getParams['noText'] ?? false);
        $this->tpl->assign('milestone', $milestone);
        $this->tpl->assign('percentDone', $percentDone);

        return 'progress';
    }

    public function showCard()
    {

        $getParams = $_GET;

        $milestone = $this->ticketService->getTicket($getParams['milestoneId']);
        $percentDone = $this->ticketService->getMilestoneProgress($getParams['milestoneId']);

        $this->tpl->assign('percentDone', $percentDone);
        $this->tpl->assign('milestone', $milestone);

    }
}
