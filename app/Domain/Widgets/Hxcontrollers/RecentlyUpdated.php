<?php

namespace Leantime\Domain\Widgets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;

/**
 * RecentlyUpdated widget — list of tickets the user touched in the last 14 days,
 * sorted by edit timestamp descending. Phase 2 / Workflow.MD §266.
 */
class RecentlyUpdated extends HtmxController
{
    protected static string $view = 'widgets::partials.recentlyUpdated';

    private TicketService $ticketsService;

    public function init(TicketService $ticketsService): void
    {
        $this->ticketsService = $ticketsService;
    }

    public function get(): void
    {
        $userId  = (int) session('userdata.id');
        $tickets = $this->ticketsService->getRecentlyUpdatedTicketsForUser($userId, 10, 14);

        $this->tpl->assign('tickets', $tickets);
    }
}
