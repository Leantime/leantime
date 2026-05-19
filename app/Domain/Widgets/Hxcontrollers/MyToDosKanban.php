<?php

namespace Leantime\Domain\Widgets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;

/**
 * Class MyToDosKanban
 *
 * HTMX controller for the personal Kanban widget on the developer dashboard.
 * Shows tickets assigned to the current user grouped by status column.
 * Each card shows only task name, due date, and priority — click to see full detail.
 */
class MyToDosKanban extends HtmxController
{
    protected static string $view = 'widgets::partials.myToDosKanban';

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
     * Load the kanban board with tickets assigned to the current user, grouped by status.
     *
     * @api
     */
    public function get(): void
    {
        $userId = (int) session('userdata.id');

        $projectFilter = session('userHomeProjectFilter', '');
        $params = $this->incomingRequest->query->all();

        if (isset($params['projectFilter'])) {
            $projectFilter = $params['projectFilter'] !== 'all' ? $params['projectFilter'] : '';
            session(['userHomeProjectFilter' => $projectFilter]);
        }

        // Status columns for the kanban board
        $kanbanColumns = $this->ticketsService->getKanbanColumns();
        $allTicketStates = $this->ticketsService->getStatusLabels();

        // Fetch open tickets assigned to the current user
        $searchCriteria = $this->ticketsService->prepareTicketSearchArray([
            'currentProject' => $projectFilter,
            'users' => $userId,
            'status' => 'not_done',
            'sprint' => '',
            'excludeType' => 'milestone',
        ]);

        $allTickets = $this->ticketsService->getAll($searchCriteria);
        if (! is_array($allTickets)) {
            $allTickets = [];
        }

        $allAssignedProjects = $this->projectService->getProjectsAssignedToUser($userId, 'open');
        $priorities = $this->ticketsService->getPriorityLabels();

        $this->tpl->assign('kanbanColumns', $kanbanColumns);
        $this->tpl->assign('allTicketStates', $allTicketStates);
        $this->tpl->assign('allTickets', $allTickets);
        $this->tpl->assign('priorities', $priorities);
        $this->tpl->assign('projectFilter', $projectFilter);
        $this->tpl->assign('allAssignedprojects', $allAssignedProjects);
    }

    /**
     * Update a ticket's status when dragged to a different kanban column.
     * The main Admin Kanban posts to the JSON-RPC endpoint; this mirrors that approach.
     *
     * @api
     */
    public function updateStatus(): void
    {
        $params = $this->incomingRequest->request->all();

        if (isset($params['id']) && isset($params['status'])) {
            $ticketId = (int) $params['id'];
            $status = (int) $params['status'];

            $result = $this->ticketsService->patch($ticketId, ['status' => $status]);

            if ($result) {
                $this->tpl->setNotification($this->language->__('short_notifications.status_updated'), 'success');
            } else {
                $this->tpl->setNotification($this->language->__('notifications.status_update_error'), 'error');
            }
        }

        $this->get();
    }
}
