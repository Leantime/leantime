<?php

namespace Leantime\Domain\Widgets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
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
        session(['lastPage' => BASE_URL.'/dashboard/home']);
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
            $this->tpl->setNotification($this->language->__('notifications.group_changes_applied'), 'success');
        } elseif ($result['successCount'] > 0 && $result['errorCount'] > 0) {
            $this->tpl->setNotification($this->language->__('notifications.group_changes_partial'), 'warning');
        } elseif ($result['errorCount'] > 0) {
            $this->tpl->setNotification($this->language->__('notifications.group_changes_failed'), 'error');
        }

        if (! $result['sorted']) {
            $this->tpl->setNotification($this->language->__('notifications.sorting_error'), 'error');
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
     * Update task status via HTMX.
     */
    public function updateStatus()
    {
        $params = $this->incomingRequest->request->all();

        if (isset($params['id']) && isset($params['status'])) {
            $result = $this->ticketsService->patch($params['id'], ['status' => $params['status']]);

            if ($result) {
                $this->tpl->setNotification($this->language->__('short_notifications.status_updated'), 'success');
            } else {
                $this->tpl->setNotification($this->language->__('notifications.status_update_error'), 'error');
            }
        }
    }

    /**
     * Update task milestone via HTMX.
     */
    public function updateMilestone()
    {
        $params = $this->incomingRequest->request->all();

        if (isset($params['id']) && isset($params['milestoneId'])) {
            $result = $this->ticketsService->patch($params['id'], ['milestoneid' => $params['milestoneId']]);

            if ($result) {
                $this->tpl->setNotification($this->language->__('notifications.milestone_updated'), 'success');
            } else {
                $this->tpl->setNotification($this->language->__('notifications.milestone_update_error'), 'error');
            }
        }
    }

    /**
     * Update task due date via HTMX.
     */
    public function updateDueDate()
    {
        $params = $this->incomingRequest->request->all();

        if (isset($params['id']) && isset($params['date'])) {
            $result = $this->ticketsService->patch($params['id'], ['dateToFinish' => $params['date']]);

            if ($result) {
                $this->tpl->setNotification($this->language->__('notifications.date_updated'), 'success');
            } else {
                $this->tpl->setNotification($this->language->__('notifications.date_update_error'), 'error');
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
        if (isset($params['id']) && isset($params['headline'])) {
            $headline = $params['headline'];

            $result = $this->ticketsService->patch($params['id'], ['headline' => $headline]);

            if ($result) {
                $this->tpl->setNotification($this->language->__('notifications.title_updated'), 'success');
            } else {
                $this->tpl->setNotification($this->language->__('notifications.title_update_error'), 'error');
            }

            return $this->tpl->displayRaw("{$headline}");
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
