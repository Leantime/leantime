<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;

/**
 * HxController for the spreadsheet-style table grid.
 *
 * Handles quick-add tasks, inline milestone/sprint creation,
 * subtask loading, and drag-and-drop reorder persistence.
 */
class TableGrid extends HtmxController
{
    protected static string $view = 'tickets::partials.tableGridSubtasks';

    private TicketService $ticketService;

    private SprintService $sprintService;

    /**
     * Initialize controller dependencies.
     */
    public function init(TicketService $ticketService, SprintService $sprintService): void
    {
        $this->ticketService = $ticketService;
        $this->sprintService = $sprintService;
    }

    /**
     * Quick-add a new ticket from the inline table row.
     *
     * Expects POST params: headline, projectId, status, and optionally
     * milestone, sprint, priority, editorId, dateToFinish.
     */
    public function quickAdd(): void
    {
        if (! AuthService::userHasRole([Roles::$owner, Roles::$manager, Roles::$editor])) {
            $this->tpl->setNotification($this->language->__('notifications.no_permission'), 'error');

            return;
        }

        $params = $_POST;
        $params['quickadd'] = true;

        $result = $this->ticketService->quickAddTicket($params);

        if (is_array($result) && isset($result['status'])) {
            $this->tpl->setNotification($result['message'], $result['status']);
        } elseif ($result !== false) {
            $this->tpl->setNotification($this->language->__('notifications.ticket_saved'), 'success');
        } else {
            $this->tpl->setNotification($this->language->__('notifications.ticket_save_error'), 'error');
        }

        $this->setHTMXEvent('HTMX.ShowNotification');
    }

    /**
     * Create a milestone inline from the table grid.
     *
     * Expects POST params: headline, editFrom, editTo, projectId.
     */
    public function addMilestone(): void
    {
        if (! AuthService::userHasRole([Roles::$owner, Roles::$manager, Roles::$editor])) {
            $this->tpl->setNotification($this->language->__('notifications.no_permission'), 'error');

            return;
        }

        $params = $_POST;
        $params['tags'] = $params['tags'] ?? 'var(--accent1)'; // Default milestone color

        $result = $this->ticketService->quickAddMilestone($params);

        if (is_array($result) && isset($result['status'])) {
            $this->tpl->setNotification($result['message'], $result['status']);
        } elseif ($result !== false) {
            $this->tpl->setNotification($this->language->__('notifications.milestone_saved'), 'success');
        } else {
            $this->tpl->setNotification($this->language->__('notifications.milestone_save_error'), 'error');
        }

        $this->setHTMXEvent('HTMX.ShowNotification');
    }

    /**
     * Create a sprint inline from the table grid.
     *
     * Expects POST params: name, startDate, endDate, projectId.
     */
    public function addSprint(): void
    {
        if (! AuthService::userHasRole([Roles::$owner, Roles::$manager, Roles::$editor])) {
            $this->tpl->setNotification($this->language->__('notifications.no_permission'), 'error');

            return;
        }

        $params = $_POST;
        $params['projectId'] = $params['projectId'] ?? session('currentProject');

        $result = $this->sprintService->addSprint($params);

        if ($result !== false) {
            $this->tpl->setNotification($this->language->__('notifications.sprint_saved'), 'success');
        } else {
            $this->tpl->setNotification($this->language->__('notifications.sprint_save_error'), 'error');
        }

        $this->setHTMXEvent('HTMX.ShowNotification');
    }

    /**
     * Load subtasks for a ticket (for child row expansion).
     *
     * Expects GET param: ticketId
     */
    public function getSubtasks(): void
    {
        $ticketId = (int) ($_GET['ticketId'] ?? 0);

        if ($ticketId <= 0) {
            return;
        }

        $subtasks = $this->ticketService->getAllSubtasks($ticketId);
        $statusLabels = $this->ticketService->getStatusLabels(session('currentProject'));
        $efforts = $this->ticketService->getEffortLabels();

        $this->tpl->assign('parentTicketId', $ticketId);
        $this->tpl->assign('subtasks', $subtasks);
        $this->tpl->assign('statusLabels', $statusLabels);
        $this->tpl->assign('efforts', $efforts);
    }

    /**
     * Add a subtask to a ticket from the table grid.
     *
     * Expects POST params: headline, parentTicketId, projectId, status.
     */
    public function addSubtask(): void
    {
        if (! AuthService::userHasRole([Roles::$owner, Roles::$manager, Roles::$editor])) {
            $this->tpl->setNotification($this->language->__('notifications.no_permission'), 'error');

            return;
        }

        $params = $_POST;
        $parentTicketId = (int) ($params['parentTicketId'] ?? 0);

        if ($parentTicketId <= 0) {
            $this->tpl->setNotification('Missing parent ticket', 'error');

            return;
        }

        $parentTicket = $this->ticketService->getTicket($parentTicketId);

        if ($parentTicket === false) {
            $this->tpl->setNotification('Parent ticket not found', 'error');

            return;
        }

        $subtaskData = [
            'headline' => $params['headline'] ?? '',
            'subtaskId' => 'new',
            'subtaskSave' => 1,
            'status' => $params['status'] ?? 3,
        ];

        if ($this->ticketService->upsertSubtask($subtaskData, $parentTicket)) {
            $this->tpl->setNotification($this->language->__('notifications.subtask_saved'), 'success');
        } else {
            $this->tpl->setNotification($this->language->__('notifications.subtask_save_error'), 'error');
        }

        // Re-render subtask list for the parent
        $subtasks = $this->ticketService->getAllSubtasks($parentTicketId);
        $statusLabels = $this->ticketService->getStatusLabels(session('currentProject'));
        $efforts = $this->ticketService->getEffortLabels();

        $this->tpl->assign('parentTicketId', $parentTicketId);
        $this->tpl->assign('subtasks', $subtasks);
        $this->tpl->assign('statusLabels', $statusLabels);
        $this->tpl->assign('efforts', $efforts);

        $this->setHTMXEvent('HTMX.ShowNotification');
    }

    /**
     * Save new sort order after drag-and-drop reorder.
     *
     * Expects JSON POST body with: updates (array of {id, sortIndex}),
     * movedTicketId, newGroupKey, groupBy.
     */
    public function reorder(): void
    {
        if (! AuthService::userHasRole([Roles::$owner, Roles::$manager, Roles::$editor])) {
            $this->tpl->setNotification($this->language->__('notifications.no_permission'), 'error');

            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (! is_array($input) || ! isset($input['updates'])) {
            return;
        }

        $updates = $input['updates'] ?? [];
        $movedTicketId = (int) ($input['movedTicketId'] ?? 0);
        $newGroupKey = $input['newGroupKey'] ?? null;
        $groupBy = $input['groupBy'] ?? '';

        // Batch update sort indexes
        $sortUpdates = [];
        foreach ($updates as $update) {
            $sortUpdates[] = [
                'id' => (int) $update['id'],
                'sortindex' => (int) $update['sortIndex'],
            ];
        }

        if (! empty($sortUpdates)) {
            try {
                $this->ticketService->updateTicketsSortIndex($sortUpdates);
            } catch (\Exception $e) {
                Log::error($e);
            }
        }

        // If the moved ticket changed groups, update the appropriate field
        if ($movedTicketId > 0 && $newGroupKey !== null && $groupBy !== '') {
            $fieldMap = [
                'milestoneid' => 'milestoneid',
                'sprint' => 'sprint',
                'priority' => 'priority',
                'editorId' => 'editorId',
                'status' => 'status',
            ];

            if (isset($fieldMap[$groupBy])) {
                $this->ticketService->patch($movedTicketId, [
                    $fieldMap[$groupBy] => $newGroupKey,
                ]);
            }
        }

        $this->tpl->setNotification($this->language->__('notifications.ticket_saved'), 'success');
        $this->setHTMXEvent('HTMX.ShowNotification');
    }
}
