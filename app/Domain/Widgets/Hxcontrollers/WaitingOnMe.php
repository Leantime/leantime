<?php

namespace Leantime\Domain\Widgets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;

/**
 * WaitingOnMe widget — tickets assigned to the user that are in WAITING status
 * or have gone stale (no update in 7+ days). Phase 2 / Workflow.MD §266.
 */
class WaitingOnMe extends HtmxController
{
    protected static string $view = 'widgets::partials.waitingOnMe';

    private TicketService $ticketsService;

    public function init(TicketService $ticketsService): void
    {
        $this->ticketsService = $ticketsService;
    }

    public function get(): void
    {
        $userId = (int) session('userdata.id');
        $tickets = $this->ticketsService->getWaitingTicketsForUser($userId, 10, 7);

        $this->tpl->assign('tickets', $tickets);
    }
}
