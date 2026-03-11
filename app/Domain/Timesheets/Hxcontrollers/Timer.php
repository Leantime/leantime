<?php

namespace Leantime\Domain\Timesheets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 * Timer HxController
 *
 * Serves the per-ticket timer component in both display variants:
 *   - 'link'   → <li> row for dropdown menus  (ticket-submenu, ticket modal)
 *   - 'button' → <div> icon for inline use     (todo widget, kanban cards)
 *
 * The variant is passed as a query-string parameter (?variant=link|button) so
 * that self-refresh via timerUpdate preserves the correct visual mode.
 *
 * @api
 */
class Timer extends HtmxController
{
    protected static string $view = 'timesheets::components.timer';

    private Timesheets $timesheetService;

    public function init(Timesheets $timesheetService): void
    {
        $this->timesheetService = $timesheetService;
    }

    /**
     * Return the timer component for a specific ticket.
     *
     * Route: GET /hx/timesheets/timer/get-status/{ticketId}?variant=link|button
     *
     * @api
     */
    public function getStatus(): void
    {
        $params = $this->incomingRequest->query->all();

        $parentTicketId = (int) ($params['request_parts'] ?? 0);
        $variant = in_array($params['variant'] ?? '', ['link', 'button']) ? $params['variant'] : 'button';

        $onTheClock = session()->exists('userdata')
            ? $this->timesheetService->isClocked(session('userdata.id'))
            : false;

        $this->tpl->assign('parentTicketId', $parentTicketId);
        $this->tpl->assign('onTheClock', $onTheClock);
        $this->tpl->assign('variant', $variant);
    }
}
