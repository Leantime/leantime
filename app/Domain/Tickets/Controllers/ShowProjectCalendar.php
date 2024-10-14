<?php

/**
 * showAll Class - show My Calender
 */

namespace Leantime\Domain\Tickets\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;

class ShowProjectCalendar extends Controller
{
    private TicketService $ticketService;

    /**
     * init - initialize private variables
     */
    public function init(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;

        session(['lastPage' => CURRENT_URL]);
        session(['lastMilestoneView' => 'calendar']);
    }

    /**
     * get - handle get requests
     */
    public function get($params)
    {
        $template_assignments = $this->ticketService->getTicketTemplateAssignments($params);
        array_map([$this->tpl, 'assign'], array_keys($template_assignments), array_values($template_assignments));

        $allProjectMilestones = $this->ticketService->getAllMilestones($template_assignments['searchCriteria']);
        $this->tpl->assign('milestones', $allProjectMilestones);

        return $this->tpl->display('tickets.calendar');
    }

    /**
     * post - handle post requests
     */
    public function post($params)
    {
        $allProjectMilestones = $this->ticketService->getAllMilestones(['sprint' => '', 'type' => 'milestone', 'currentProject' => session('currentProject')]);
        $this->tpl->assign('milestones', $allProjectMilestones);

        return $this->tpl->display('tickets.roadmap');
    }
}
