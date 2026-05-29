<?php

namespace Leantime\Domain\Tickets\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;

class Roadmap extends Controller
{
    private TicketService $ticketService;

    /**
     * init - initialize private variables
     */
    public function init(
        TicketService $ticketService
    ): void {
        $this->ticketService = $ticketService;

        session(['lastPage' => CURRENT_URL]);
        session(['lastMilestoneView' => 'timeline']);
        session(['lastFilterdMilestonesView' => CURRENT_URL]);
    }

    /**
     * get - handle get requests
     */
    public function get($params)
    {
        $params = $this->ticketService->normalizeRoadmapParams($params);

        // Sets the filter module to show a quick toggle for task types
        $this->tpl->assign('enableTaskTypeToggle', true);
        $this->tpl->assign('showTasks', $params['showTasks'] ?? 'false');

        $template_assignments = $this->ticketService->getTicketTemplateAssignments($params);

        array_map([$this->tpl, 'assign'], array_keys($template_assignments), array_values($template_assignments));

        $allProjectMilestones = $this->ticketService->getAllMilestones($template_assignments['searchCriteria'], 'standard');
        $allProjectMilestones = $this->ticketService->getBulkMilestoneProgress($allProjectMilestones);

        $this->tpl->assign('timelineTasks', $allProjectMilestones);

        return $this->tpl->display('tickets.roadmap');
    }

    /**
     * post - handle post requests
     */
    public function post($params)
    {

        $template_assignments = $this->ticketService->getTicketTemplateAssignments($params);
        array_map([$this->tpl, 'assign'], array_keys($template_assignments), array_values($template_assignments));

        $allProjectMilestones = $this->ticketService->getAllMilestones($template_assignments['searchCriteria']);

        $this->tpl->assign('timelineTasks', $allProjectMilestones);

        return $this->tpl->display('tickets.roadmap');
    }
}
