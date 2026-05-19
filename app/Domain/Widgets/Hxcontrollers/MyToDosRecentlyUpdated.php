<?php

namespace Leantime\Domain\Widgets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;

/**
 * Class MyToDosRecentlyUpdated
 *
 * HTMX controller for the Recently Updated view on the developer dashboard My Todos widget.
 * Shows the current user's tasks sorted by last-updated date, last 14 days.
 */
class MyToDosRecentlyUpdated extends HtmxController
{
    protected static string $view = 'widgets::partials.myToDosRecentlyUpdated';

    private TicketService $ticketsService;

    private ProjectService $projectService;

    public function init(
        TicketService $ticketsService,
        ProjectService $projectService
    ): void {
        $this->ticketsService = $ticketsService;
        $this->projectService = $projectService;
        session(['lastPage' => BASE_URL.'/dashboard/home']);
    }

    /**
     * Load the recently updated tasks for the current user.
     *
     * @api
     */
    public function get(): void
    {
        $userId = (int) session('userdata.id');

        $tickets = $this->ticketsService->getRecentlyUpdatedTicketsForUser($userId, 25, 14);

        $this->tpl->assign('tickets', is_array($tickets) ? $tickets : []);
        $this->tpl->assign('priorities', $this->ticketsService->getPriorityLabels());
        $this->tpl->assign('allAssignedprojects', $this->projectService->getProjectsAssignedToUser($userId, 'open'));
    }
}
