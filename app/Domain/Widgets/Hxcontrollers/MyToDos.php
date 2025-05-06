<?php

namespace Leantime\Domain\Widgets\Hxcontrollers;

use Leantime\Core\Http\Controller\HtmxController;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Setting\Services\Setting;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;

/**
 * Class MyToDos
 *
 * This class extends the HtmxController class and represents a controller for managing to-do items.
 */
class MyToDos extends HtmxController
{
    protected static string $view = 'widgets::partials.myToDos';

    private TicketService $ticketsService;

    private Setting $settingsService;

    public function init(
        TicketService $ticketsService,
        Setting $settingsService,
    ) {
        $this->ticketsService = $ticketsService;
        $this->settingsService = $settingsService;
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

        // Get hierarchical tasks
        $tplVars = $this->ticketsService->getToDoWidgetHierarchicalAssignments($params);

        // Get user's personal sorting preferences
        $userId = session('userdata.id');
        $sortingKey = "user.{$userId}.myTodosSorting";
        $sorting = $this->settingsService->getSetting($sortingKey);

        if ($sorting) {
            $tplVars['sorting'] = json_decode($sorting, true);
        } else {
            $tplVars['sorting'] = [];
        }

        $tplVars['sorting'] = collect($tplVars['sorting']);

        array_map([$this->tpl, 'assign'], array_keys($tplVars), array_values($tplVars));
    }

    /**
     * Save the user's personal task sorting preferences
     */
    public function saveSorting($params)
    {
        $post = $_POST;
        $userId = session('userdata.id');
        unset($params['act']);

        if (is_array($params)) {
            $taskList = array_map(function ($item) {
                $task = json_decode($item, true);
                if (is_array($task) && isset($task['id'])) {
                    // start sorting at 10 so we have room for new tasks at the top
                    $task['order'] += 10;

                    return $task;
                }
            }, $params);

            $sortingKey = "user.{$userId}.myTodosSorting";
            $this->settingsService->saveSetting($sortingKey, json_encode($taskList));
            $this->updateTicketDependencies($taskList);

            // Return success response without reloading the entire widget
            return;
        }

        $this->tpl->setNotification($this->language->__('notifications.sorting_error'), 'error');
    }

    /**
     * Toggle the collapse state of a task
     */
    public function toggleTaskCollapse($params)
    {
        if (isset($params['taskId'])) {
            $taskId = $params['taskId'];
            $userId = session('userdata.id');
            $toggleKey = "user.{$userId}.taskCollapsed.{$taskId}";

            // Get current state
            $currentState = $this->settingsService->getSetting($toggleKey, 'open');

            // Toggle the state
            $newState = ($currentState === 'open') ? 'closed' : 'open';
            $this->settingsService->saveSetting($toggleKey, $newState);

            return $newState;
        }
    }

    /**
     * Update ticket dependencies based on the sorting hierarchy
     *
     * @param  array  $sorting  The sorting data with parent-child relationships
     */
    private function updateTicketDependencies($sorting)
    {
        // Create a map of ticket IDs to their parent IDs
        $parentMap = [];
        foreach ($sorting as $item) {
            if (isset($item['id']) && isset($item['parentId']) && $item['parentId'] !== null) {
                $parentMap[$item['id']]['parentId'] = $item['parentId'];
                $parentMap[$item['id']]['parentType'] = $item['parentType'];
            } elseif (isset($item['id'])) {
                // If no parent, ensure we clear any existing dependency
                $parentMap[$item['id']]['parentId'] = 0;
                $parentMap[$item['id']]['parentType'] = '';
            }
        }

        // Update each ticket's dependencies
        foreach ($parentMap as $ticketId => $parent) {
            // Skip if the parent is the same as the ticket (prevent self-reference)
            if ($ticketId == $parent['parentId']) {
                continue;
            }

            $parentId = $parent['parentId'];
            $parentType = $parent['parentType'];

            // For tickets with parents, set the dependingTicketId
            if ($parentId > 0) {
                $field = $parentType == 'milestone' ? 'milestoneid' : 'dependingTicketId';

                $this->ticketsService->patch($ticketId, [
                    'dependingTicketId' => $parentId,
                ]);
            } else {
                // For tickets without parents, clear the dependingTicketId
                $this->ticketsService->patch($ticketId, [
                    'dependingTicketId' => '',
                    'milestoneid' => '',
                ]);
            }
        }
    }

    /**
     * Update task status via HTMX
     */
    public function updateStatus()
    {
        $params = $this->incomingRequest->request->all();

        if (isset($params['id']) && isset($params['status'])) {
            $ticketId = $params['id'];
            $status = $params['status'];

            $result = $this->ticketsService->patch($ticketId, ['status' => $status]);

            if ($result) {
                $this->tpl->setNotification($this->language->__('short_notifications.status_updated'), 'success');
            } else {
                $this->tpl->setNotification($this->language->__('notifications.status_update_error'), 'error');
            }
        }
    }

    /**
     * Update task milestone via HTMX
     */
    public function updateMilestone()
    {
        $params = $this->incomingRequest->request->all();

        if (isset($params['id']) && isset($params['milestoneId'])) {
            $ticketId = $params['id'];
            $milestoneId = $params['milestoneId'];

            $result = $this->ticketsService->patch($ticketId, ['milestoneid' => $milestoneId]);

            if ($result) {
                $this->tpl->setNotification($this->language->__('notifications.milestone_updated'), 'success');
            } else {
                $this->tpl->setNotification($this->language->__('notifications.milestone_update_error'), 'error');
            }
        }
    }

    /**
     * Update task due date via HTMX
     */
    public function updateDueDate()
    {
        $params = $this->incomingRequest->request->all();

        if (isset($params['id']) && isset($params['date'])) {
            $ticketId = $params['id'];
            $date = $params['date'];

            $result = $this->ticketsService->patch($ticketId, ['dateToFinish' => $date]);

            if ($result) {
                $this->tpl->setNotification($this->language->__('notifications.date_updated'), 'success');
            } else {
                $this->tpl->setNotification($this->language->__('notifications.date_update_error'), 'error');
            }
        }
    }

    /**
     * Update task due date via HTMX
     */
    public function updateTitle($params)
    {
        if (isset($params['id']) && isset($params['headline'])) {
            $ticketId = $params['id'];
            $headline = $params['headline'];

            $result = $this->ticketsService->patch($ticketId, ['headline' => $headline]);

            if ($result) {
                $this->tpl->setNotification($this->language->__('notifications.title_updated'), 'success');
            } else {
                $this->tpl->setNotification($this->language->__('notifications.title_update_error'), 'error');
            }

            return $this->tpl->displayRaw("{$headline}");
        }
    }

    /**
     * Handle subtask creation
     */
    public function addSubtask()
    {
        $params = $_POST;
        $getParams = $_GET;

        // Use the existing subtasks controller to handle the creation
        $ticket = $this->ticketsService->getTicket($getParams['ticketId']);

        if ($this->ticketsService->upsertSubtask($params, $ticket)) {
            $this->tpl->setNotification($this->language->__('notifications.subtask_saved'), 'success');
        } else {
            $this->tpl->setNotification($this->language->__('notifications.subtask_save_error'), 'error');
        }

        // Refresh the todo widget
        $tplVars = $this->ticketsService->getToDoWidgetHierarchicalAssignments($params);
        array_map([$this->tpl, 'assign'], array_keys($tplVars), array_values($tplVars));
    }

    public function addTodo()
    {
        $params = $_POST;

        if (AuthService::userHasRole([Roles::$owner, Roles::$manager, Roles::$editor])) {
            if (isset($params['quickadd']) == true) {
                // Set appropriate due date based on group context
                if (isset($params['dateToFinish']) && $params['dateToFinish'] == '') {
                    // If no specific date was set, determine based on group
                    if (isset($params['group'])) {
                        if ($params['group'] == 'thisWeek') {
                            // Due this week - set to end of week (Friday)
                            $params['dateToFinish'] = date('Y-m-d', strtotime('next friday'));
                        } elseif ($params['group'] == 'overdue') {
                            // Overdue - set to today
                            $params['dateToFinish'] = date('Y-m-d');
                        }
                        // For 'later' group, leave date empty
                    }
                }

                $result = $this->ticketsService->quickAddTicket($params);

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
}
