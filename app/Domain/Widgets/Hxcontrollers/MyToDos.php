<?php

namespace Leantime\Domain\Widgets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\NotFoundException;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Widgets\Services\Dashboard as DashboardService;

/**
 * Class MyToDos
 *
 * This class extends the HtmxController class and represents a controller for managing to-do items.
 */
class MyToDos extends HtmxController
{
    protected static string $view = 'widgets::partials.myToDos';

    private TicketService $ticketsService;

    private DashboardService $dashboardService;

    private int $limit = 50;

    /**
     * Initializes dependencies.
     *
     * @param  TicketService  $ticketsService  The tickets service.
     * @param  DashboardService  $dashboardService  The dashboard orchestration service.
     */
    public function init(
        TicketService $ticketsService,
        DashboardService $dashboardService,
    ): void {
        $this->ticketsService = $ticketsService;
        $this->dashboardService = $dashboardService;
    }

    /**
     * Retrieves the todo widget assignments.
     *
     * @return void
     */
    public function get()
    {
        $params = $this->incomingRequest->query->all();

        // Set initial pagination - only load first page of tasks per group
        if (! isset($params['limit'])) {
            $params['limit'] = $this->limit;
        }

        $tplVars = $this->dashboardService->getToDoWidgetData((int) session('userdata.id'), $params);

        $this->tpl->assign('limit', $tplVars['limit']);

        array_map([$this->tpl, 'assign'], array_keys($tplVars), array_values($tplVars));
    }

    /**
     * Save the user's personal task sorting preferences.
     *
     * @param  mixed  $params  The sort items posted by the request.
     */
    public function saveSorting($params)
    {
        $post = $this->incomingRequest->request->all();

        if (is_array($params)) {
            unset($params['act']);
        }

        $result = $this->dashboardService->saveTodoSorting(
            (int) session('userdata.id'),
            $params,
            $post['groupChanges'] ?? [],
            $post['groupBy'] ?? ''
        );

        if ($result['successCount'] > 0 && $result['errorCount'] === 0) {
            $this->tpl->setNotification($this->language->__('short_notifications.group_changes_applied'), 'success');
        } elseif ($result['successCount'] > 0 && $result['errorCount'] > 0) {
            $this->tpl->setNotification($this->language->__('short_notifications.group_changes_partial'), 'warning');
        } elseif ($result['errorCount'] > 0) {
            $this->tpl->setNotification($this->language->__('short_notifications.group_changes_failed'), 'error');
        }

        if (! $result['sorted']) {
            $this->tpl->setNotification($this->language->__('short_notifications.sorting_error'), 'error');
        }
    }

    /**
     * Toggle the collapse state of a task.
     *
     * @param  array  $params  Request parameters containing the taskId.
     * @return string|void The new collapse state when a taskId is provided.
     */
    public function toggleTaskCollapse($params)
    {
        if (isset($params['taskId'])) {
            return $this->dashboardService->toggleTaskCollapse((int) session('userdata.id'), $params['taskId']);
        }
    }

    /**
     * Update task due date via HTMX.
     */
    public function updateDueDate()
    {
        $params = $this->incomingRequest->request->all();

        if (isset($params['id']) && isset($params['date'])) {
            $result = $this->patchTask($params['id'], ['dateToFinish' => $params['date']]);

            if ($result) {
                $this->tpl->setNotification($this->language->__('short_notifications.date_updated'), 'success');
            } else {
                $this->tpl->setNotification($this->language->__('short_notifications.date_update_error'), 'error');
            }
        }
    }

    /**
     * Update task title via HTMX.
     *
     * @param  array  $params  Request parameters containing id and headline.
     * @return mixed The raw rendered headline when an id and headline are provided.
     */
    public function updateTitle($params)
    {
        if (isset($params['id']) && isset($params['headline']) && is_scalar($params['headline'])) {
            $headline = (string) $params['headline'];

            $result = $this->patchTask($params['id'], ['headline' => $headline]);

            if ($result) {
                $this->tpl->setNotification($this->language->__('short_notifications.title_updated'), 'success');
            } else {
                $this->tpl->setNotification($this->language->__('short_notifications.title_update_error'), 'error');
            }

            // The response is swapped into the page as HTML, so the echoed headline must be escaped.
            return $this->tpl->displayRaw(e($headline));
        }
    }

    /**
     * Patch a task through the authorized service entry point.
     *
     * patchTicket() resolves the ticket's real project and requires tickets.edit there, so a
     * read-only member (or a caller with a foreign ticket id) gets a failed update instead of
     * a write. Denials and invalid input are mapped to false so the widget shows its normal error notification.
     *
     * @param  mixed  $ticketId  The ticket id from the request.
     * @param  array<string, mixed>  $values  The fields to update.
     * @return bool True when the ticket was updated.
     */
    private function patchTask(mixed $ticketId, array $values): bool
    {
        if (! is_numeric($ticketId) || (int) $ticketId <= 0) {
            return false;
        }

        try {
            return $this->ticketsService->patchTicket((int) $ticketId, $values);
        } catch (AuthorizationException|NotFoundException|ValidationException) {
            return false;
        }
    }

    /**
     * Handle subtask creation.
     */
    public function addSubtask()
    {
        $params = $this->incomingRequest->request->all();
        $getParams = $this->incomingRequest->query->all();

        if ($this->dashboardService->addSubtask($params, (int) $getParams['ticketId'])) {
            $this->tpl->setNotification($this->language->__('notifications.subtask_saved'), 'success');
        } else {
            $this->tpl->setNotification($this->language->__('notifications.subtask_save_error'), 'error');
        }

        // Refresh the todo widget
        $tplVars = $this->ticketsService->getToDoWidgetHierarchicalAssignments($params);
        array_map([$this->tpl, 'assign'], array_keys($tplVars), array_values($tplVars));
    }

    /**
     * Quick-add a to-do and refresh the widget.
     */
    public function addTodo()
    {
        $params = $this->incomingRequest->request->all();

        if (AuthService::userHasRole([Roles::$owner, Roles::$manager, Roles::$editor])) {
            if (isset($params['quickadd']) == true) {
                $result = $this->dashboardService->addTodo($params);

                if (isset($result['status'])) {
                    $this->tpl->setNotification($result['message'], $result['status']);
                } else {
                    $this->tpl->setNotification($this->language->__('notifications.ticket_saved'), 'success');
                }

                $this->tpl->setHTMXEvent('HTMX.ShowNotification');
            }
        }

        $tplVars = $this->ticketsService->getToDoWidgetHierarchicalAssignments($params);
        array_map([$this->tpl, 'assign'], array_keys($tplVars), array_values($tplVars));
    }

    /**
     * Load more todos for infinite scroll.
     */
    public function loadMore()
    {
        $params = $this->incomingRequest->query->all();

        $tplVars = $this->dashboardService->getToDoWidgetLoadMoreData((int) session('userdata.id'), $params, $this->limit);

        $this->tpl->assign('limit', $tplVars['limit']);
        array_map([$this->tpl, 'assign'], array_keys($tplVars), array_values($tplVars));
    }
}
