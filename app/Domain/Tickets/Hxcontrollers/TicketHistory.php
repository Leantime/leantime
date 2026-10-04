<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HxComponent;
use Leantime\Domain\Tickets\Services\TicketHistory as TicketHistoryService;

/**
 * Change history list of a ticket (the "History" tab of the ticket modal).
 *
 * Mounted lazily with <x-global::hx :for="self::class" :id="$ticketId" trigger="intersect once" />
 * so the history is only fetched when the tab is first shown.
 */
class TicketHistory extends HxComponent
{
    protected static string $view = 'tickets::partials.ticketHistory';

    /** Swap the inner content so the mount wrapper stays in place. */
    public static string $swap = 'innerHTML';

    private TicketHistoryService $ticketHistoryService;

    /**
     * Controller dependencies.
     */
    public function init(TicketHistoryService $ticketHistoryService): void
    {
        $this->ticketHistoryService = $ticketHistoryService;
    }

    /**
     * The hx route segment.
     */
    public static function route(): string
    {
        return 'tickets/ticketHistory';
    }

    /**
     * Render the history of the ticket given as id. The service authorizes against the ticket's
     * own project; a denied or missing ticket renders the "not available" state.
     *
     * @param  array<string, mixed>  $params  Request params (id = ticket id)
     */
    public function get(array $params): void
    {
        $ticketId = (int) ($params['id'] ?? 0);

        $historyEntries = $this->ticketHistoryService->getTicketHistory($ticketId);

        $this->tpl->assign('historyAvailable', $historyEntries !== false);
        $this->tpl->assign('historyEntries', $historyEntries === false ? [] : $historyEntries);
    }
}
