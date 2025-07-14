<?php

namespace Leantime\Domain\Widgets\Hxcontrollers;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Controller\HtmxController;
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

    private int $limit = 50;

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

        // Set initial pagination - only load first 20 tasks per group
        if (! isset($params['limit'])) {
            $params['limit'] = $this->limit;
        }

        // Get hierarchical tasks
        $tplVars = $this->ticketsService->getToDoWidgetHierarchicalAssignments($params);

        // Get user's personal sorting preferences
        $userId = session('userdata.id');
        $sortingKey = "user.{$userId}.myTodosSorting";
        $sorting = $this->settingsService->getSetting($sortingKey);

        $totalLoadedTickets = 0;

        foreach ($tplVars['tickets'] as $ticketGroup) {
            $totalLoadedTickets += collect($tplVars['tickets'])->countNested('tickets');
        }

        $hasMoreTickets = $totalLoadedTickets >= $params['limit'];

        $tplVars['hasMoreTickets'] = $hasMoreTickets;

        if ($sorting) {
            $tplVars['sorting'] = json_decode($sorting, true);
        } else {
            $tplVars['sorting'] = [];
        }

        $tplVars['sorting'] = collect($tplVars['sorting']);
        $this->tpl->assign('limit', $params['limit']);

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

        // Check if group changes are present (indicating new format)
        $hasGroupChanges = false;
        $groupChanges = [];
        $groupBy = $post['groupBy'] ?? '';

        // Extract group changes from POST data
        if (isset($post['groupChanges'])) {
            foreach ($post['groupChanges'] as $key => $value) {
                $hasGroupChanges = true;
                $groupChanges[] = json_decode($value, true);
            }
        }

        // Process group changes first if present
        if ($hasGroupChanges && ! empty($groupChanges)) {
            $this->processGroupChanges($groupChanges, $groupBy);
        }

        // Handle sorting
        if (is_array($params)) {
            $taskList = array_map(function ($item) {
                if (is_string($item)) {
                    $task = json_decode($item, true);
                    if (is_array($task) && isset($task['id'])) {
                        // start sorting at 10 so we have room for new tasks at the top
                        $task['order'] = $task['order'] ?? 0;
                        $task['order'] += 10;

                        return $task;
                    }
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
     * Process group changes and update corresponding task fields
     *
     * @param  array  $groupChanges  Array of group change data
     * @param  string  $groupBy  The grouping type (time, project, priority)
     */
    private function processGroupChanges(array $groupChanges, string $groupBy): void
    {
        $successCount = 0;
        $errorCount = 0;

        foreach ($groupChanges as $change) {
            $taskId = $change['id'] ?? null;
            $toGroup = $change['toGroup'] ?? null;
            $fromGroup = $change['fromGroup'] ?? null;

            // Skip invalid changes
            if (empty($taskId) || empty($toGroup)) {
                $errorCount++;

                continue;
            }

            // Validate that user has permission to update this task
            if (! $this->canUserUpdateTask($taskId)) {
                Log::warning("User does not have permission to update task {$taskId}");
                $errorCount++;

                continue;
            }

            $fieldsToUpdate = $this->mapGroupToFields($groupBy, $toGroup);

            if (! empty($fieldsToUpdate)) {
                try {
                    $result = $this->ticketsService->patch($taskId, $fieldsToUpdate);

                    if ($result) {
                        $successCount++;

                        // Log successful group change for debugging
                        Log::info("Successfully moved task {$taskId} from group {$fromGroup} to {$toGroup} ({$groupBy})");
                    } else {
                        $errorCount++;
                        Log::error("Failed to update task {$taskId} with group change to {$toGroup}");
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error("Error updating task {$taskId}: ".$e->getMessage());
                }
            } else {
                // No valid field mapping found
                Log::warning("No valid field mapping found for group {$toGroup} in groupBy {$groupBy}");
            }
        }

        // Set user notifications based on results
        if ($successCount > 0 && $errorCount === 0) {
            $this->tpl->setNotification($this->language->__('notifications.group_changes_applied'), 'success');
        } elseif ($successCount > 0 && $errorCount > 0) {
            $this->tpl->setNotification(
                $this->language->__('notifications.group_changes_partial'),
                'warning'
            );
        } elseif ($errorCount > 0) {
            $this->tpl->setNotification(
                $this->language->__('notifications.group_changes_failed'),
                'error'
            );
        }
    }

    /**
     * Map group key to field updates based on grouping type
     *
     * @param  string  $groupBy  The grouping type
     * @param  string  $groupKey  The target group key
     * @return array Fields to update
     */
    private function mapGroupToFields(string $groupBy, string $groupKey): array
    {
        switch ($groupBy) {
            case 'time':
                return $this->mapTimeGroupToFields($groupKey);

            case 'project':
                return $this->mapProjectGroupToFields($groupKey);

            case 'priority':
                return $this->mapPriorityGroupToFields($groupKey);

            default:
                return [];
        }
    }

    /**
     * Map time group to date fields
     *
     * @param  string  $groupKey  Time group key (overdue, thisWeek, later)
     * @return array Fields to update
     */
    private function mapTimeGroupToFields(string $groupKey): array
    {
        switch ($groupKey) {
            case 'overdue':
                // Set due date to yesterday to make it overdue
                return ['dateToFinish' => date('Y-m-d', strtotime('yesterday'))];

            case 'thisWeek':
                // Set due date to end of current week (Friday)
                return ['dateToFinish' => date('Y-m-d', strtotime('next friday'))];

            case 'later':
                // Clear due date for "later" group
                return ['dateToFinish' => ''];

            default:
                return [];
        }
    }

    /**
     * Map project group to project field
     *
     * @param  string  $groupKey  Project ID
     * @return array Fields to update
     */
    private function mapProjectGroupToFields(string $groupKey): array
    {
        // Validate that the group key is a valid project ID
        if (is_numeric($groupKey) && $groupKey > 0) {
            $projectId = (int) $groupKey;

            // Additional validation: Check if user has access to the target project
            // This could be enhanced with a proper project permission check
            // For now, we'll trust that the group was presented to the user, so they have access

            return ['projectId' => $projectId];
        }

        return [];
    }

    /**
     * Map priority group to priority field
     *
     * @param  string  $groupKey  Priority value
     * @return array Fields to update
     */
    private function mapPriorityGroupToFields(string $groupKey): array
    {
        // Handle priority mapping
        if ($groupKey === '999') {
            // 999 represents "undefined priority" - clear the priority
            return ['priority' => ''];
        }

        // Validate priority is within valid range (1-4)
        if (is_numeric($groupKey) && $groupKey >= 1 && $groupKey <= 4) {
            return ['priority' => (int) $groupKey];
        }

        return [];
    }

    /**
     * Check if the current user can update a specific task
     *
     * @param  int  $taskId  The task ID to check
     * @return bool True if user can update, false otherwise
     */
    private function canUserUpdateTask(int $taskId): bool
    {
        try {
            // Attempt to get the ticket - this will return false if user doesn't have access
            $ticket = $this->ticketsService->getTicket($taskId);

            if (! $ticket || empty($ticket)) {
                return false;
            }

            // Additional permission checks can be added here if needed
            // For now, if user can view the ticket, they can update it
            return true;

        } catch (\Exception $e) {
            Log::error("Permission check failed for task {$taskId}: ".$e->getMessage());

            return false;
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

    /**
     * Load more todos for infinite scroll
     */
    public function loadMore()
    {
        $params = $this->incomingRequest->query->all();

        // Set default pagination values
        $params['limit'] = $params['limit'] + $this->limit;
        $params['offset'] = 0;

        // Get hierarchical tasks with global pagination
        $tplVars = $this->ticketsService->getToDoWidgetHierarchicalAssignments($params);

        // Check if there are more tickets to load by seeing if we got a full page
        $totalLoadedTickets = 0;
        foreach ($tplVars['tickets'] as $ticketGroup) {
            $totalLoadedTickets += collect($tplVars['tickets'])->countNested('tickets');
        }

        $hasMoreTickets = $totalLoadedTickets >= $params['limit'];
        $tplVars['hasMoreTickets'] = $hasMoreTickets;
        $tplVars['nextOffset'] = $params['offset'] + $params['limit'];
        $tplVars['isLoadMore'] = true;
        $this->tpl->assign('limit', $params['limit']);
        array_map([$this->tpl, 'assign'], array_keys($tplVars), array_values($tplVars));

    }
}
