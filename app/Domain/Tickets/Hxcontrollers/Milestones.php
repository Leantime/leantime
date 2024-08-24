<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Core\Language;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 *
 */
class Milestones extends HtmxController
{
    /**
     * @var string
     */
    protected static string $view = 'tickets::partials.milestoneCard';

    /**
     * @var Tickets
     */
    private Tickets $ticketService;

    private Language $language;

    /**
     * Controller constructor
     *
     * @param Timesheets $timesheetService
     * @return void
     */
    public function init(Tickets $ticketService, Language $language): void
    {
        $this->ticketService = $ticketService;
        $this->language = $language;
    }

    public function progress() {

        $getParams = $_GET;

        $milestone = $this->ticketService->getTicket($getParams["milestoneId"]);
        $percentDone = $this->ticketService->getMilestoneProgress($getParams["milestoneId"]);

        $this->tpl->assign('milestone', $milestone);
        $this->tpl->assign('percentDone', $percentDone);

        return "progress";
    }

    public function showCard() {

        $getParams = $_GET;

        $milestone = $this->ticketService->getTicket($getParams["milestoneId"]);
        $percentDone = $this->ticketService->getMilestoneProgress($getParams["milestoneId"]);

        $this->tpl->assign('percentDone', $percentDone);
        $this->tpl->assign('milestone', $milestone);

    }


}
