<?php

namespace Leantime\Domain\Tickets\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Symfony\Component\HttpFoundation\Response;

class ShowAllMilestones extends Controller
{
    private TicketService $ticketService;

    public function init(
        TicketService $ticketService
    ): void {
        $this->ticketService = $ticketService;

        session(['lastPage' => CURRENT_URL]);
        session(['lastMilestoneView' => 'milestonetable']);
        session(['lastFilterdMilestoneView' => CURRENT_URL]);
    }

    /**
     * @throws \Exception
     */
    public function get($params): Response
    {

        $params = $this->ticketService->normalizeRoadmapParams($params);

        // Sets the filter module to show a quick toggle for task types
        $this->tpl->assign('enableTaskTypeToggle', true);
        $this->tpl->assign('showTasks', $params['showTasks'] ?? 'false');

        $template_assignments = $this->ticketService->getTicketTemplateAssignments($params);
        array_map([$this->tpl, 'assign'], array_keys($template_assignments), array_values($template_assignments));

        return $this->tpl->display('tickets.showAllMilestones');
    }
}
