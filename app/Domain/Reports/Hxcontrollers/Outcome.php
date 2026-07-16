<?php

declare(strict_types=1);

namespace Leantime\Domain\Reports\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Reports\Models\ReportPeriod;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;

/**
 * Inline outcome & impact capture on the report screens: saves the narrative onto the
 * milestone and re-renders the outcome block in place.
 */
class Outcome extends HtmxController
{
    protected static string $view = 'reports::partials.outcome';

    private TicketService $ticketService;

    public function init(TicketService $ticketService): void
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Persists the outcome narrative. patchTicket() authorizes editor+ against the
     * milestone's own project, so a smuggled milestone id can't cross projects.
     */
    public function save(): void
    {
        $milestoneId = (int) ($_POST['milestoneId'] ?? 0);
        $outcomeImpact = trim((string) ($_POST['outcomeImpact'] ?? ''));

        $this->ticketService->patchTicket($milestoneId, ['outcomeImpact' => $outcomeImpact]);

        $milestone = $this->ticketService->getTicket($milestoneId);

        $this->tpl->assign('milestone', $milestone);
        $this->tpl->assign('canEdit', true);
        $this->tpl->assign('period', ReportPeriod::fromRequest($_POST));
        $this->tpl->setNotification($this->language->__('notifications.outcome_saved'), 'success');
    }
}
