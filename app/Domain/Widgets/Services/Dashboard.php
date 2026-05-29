<?php

namespace Leantime\Domain\Widgets\Services;

use Illuminate\Support\Facades\Log;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Reports\Services\Reports as ReportService;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Users\Services\Users as UserService;

/**
 * Class Dashboard
 *
 * Aggregates and orchestrates the business logic behind the dashboard widgets
 * (the Welcome widget and the "My To-Dos" widget). The HxControllers in this
 * domain delegate all data access, grouping, ordering and orchestration to this
 * service so they can stay thin.
 */
class Dashboard
{
    /**
     * Constructs the dashboard service.
     *
     * @param  TicketService  $ticketsService  The tickets service.
     * @param  SettingService  $settingsService  The setting service.
     * @param  ProjectService  $projectsService  The projects service.
     * @param  UserService  $usersService  The users service.
     * @param  ReportService  $reportService  The reports service (anonymous telemetry).
     * @param  Widgets  $widgetService  The widgets service.
     */
    public function __construct(
        protected TicketService $ticketsService,
        protected SettingService $settingsService,
        protected ProjectService $projectsService,
        protected UserService $usersService,
        protected ReportService $reportService,
        protected Widgets $widgetService,
    ) {}

    /**
     * Fires the anonymous telemetry collection and waits for it to complete.
     *
     * Failures are logged and swallowed so the dashboard render path is never
     * blocked or broken by telemetry issues.
     *
     * @api
     */
    public function sendAnonymousTelemetry(): void
    {
        try {
            $promise = $this->reportService->sendAnonymousTelemetry();
            if ($promise !== false) {
                $promise->wait();
            }
        } catch (\Exception $e) {
            Log::error($e);
        }
    }

    /**
     * Aggregates all data the Welcome widget needs into a single array.
     *
     * Moves the count()/is_array guards and the today start/end computation out
     * of the controller and into the service.
     *
     * @param  int  $userId  The user the dashboard is rendered for.
     * @return array{
     *     currentUser: mixed,
     *     showSettingsIndicator: bool,
     *     totalTickets: int,
     *     closedTicketsCount: int,
     *     ticketsInGoals: int,
     *     totalTodayCount: int,
     *     doneTodayCount: int,
     *     allProjects: array<int, mixed>,
     *     projectCount: int
     * }
     *
     * @api
     */
    public function getWelcomeWidgetData(int $userId): array
    {
        $currentUser = $this->usersService->getUser($userId);

        // Check for new widgets to show settings indicator
        $showSettingsIndicator = false;
        if (session()->exists('userdata')) {
            $newWidgets = $this->widgetService->getNewWidgets($userId);
            $showSettingsIndicator = ! empty($newWidgets);
        }

        $totalTickets = $this->ticketsService->simpleTicketCounter(
            userId: $userId,
            status: 'not_done',
            types: ['task', 'story', 'bug']
        );

        $closedTicketsCount = 0;
        $closedTickets = $this->ticketsService->getRecentlyCompletedTicketsByUser($userId, null);
        if (is_array($closedTickets)) {
            $closedTicketsCount = count($closedTickets);
        }

        $ticketsInGoals = 0;
        $goalTickets = $this->ticketsService->goalsRelatedToWork($userId, null);
        if (is_array($goalTickets)) {
            $ticketsInGoals = count($goalTickets);
        }

        $todayStart = dtHelper()->userNow()->startOfDay();
        $todayEnd = dtHelper()->userNow()->endOfDay();
        $todaysTasks = $this->ticketsService->getScheduledTasks($todayStart, $todayEnd, $userId);
        $totalToday = count($todaysTasks['totalTasks'] ?? []);
        $doneToday = count($todaysTasks['doneTasks'] ?? []);

        $allAssignedProjects = $this->projectsService->getProjectsAssignedToUser($userId, 'open');
        if (! is_array($allAssignedProjects)) {
            $allAssignedProjects = [];
        }

        return [
            'currentUser' => $currentUser,
            'showSettingsIndicator' => $showSettingsIndicator,
            'totalTickets' => $totalTickets,
            'closedTicketsCount' => $closedTicketsCount,
            'ticketsInGoals' => $ticketsInGoals,
            'totalTodayCount' => $totalToday,
            'doneTodayCount' => $doneToday,
            'allProjects' => $allAssignedProjects,
            'projectCount' => count($allAssignedProjects),
        ];
    }

    /**
     * Builds the template variables for the "My To-Dos" widget, including the
     * hierarchical task assignments, the user's stored sorting preferences and
     * the pagination "has more" computation.
     *
     * @param  int  $userId  The user the widget is rendered for.
     * @param  array  $params  The request parameters (limit, offset, filters...).
     * @return array<string, mixed> Template variables including hasMoreTickets, sorting and limit.
     *
     * @api
     */
    public function getToDoWidgetData(int $userId, array $params): array
    {
        $tplVars = $this->ticketsService->getToDoWidgetHierarchicalAssignments($params);

        $tplVars['hasMoreTickets'] = $this->hasMoreTickets($tplVars['tickets'] ?? [], (int) $params['limit']);

        $tplVars['sorting'] = collect($this->getUserSorting($userId));
        $tplVars['limit'] = $params['limit'];

        return $tplVars;
    }

    /**
     * Builds the template variables for a "load more" (infinite scroll) request
     * of the "My To-Dos" widget.
     *
     * @param  int  $userId  The user the widget is rendered for.
     * @param  array  $params  The request parameters; limit is incremented by the page size.
     * @param  int  $pageSize  The number of tasks loaded per page.
     * @return array<string, mixed> Template variables including hasMoreTickets, nextOffset, isLoadMore and limit.
     *
     * @api
     */
    public function getToDoWidgetLoadMoreData(int $userId, array $params, int $pageSize): array
    {
        $params['limit'] = $params['limit'] + $pageSize;
        $params['offset'] = 0;

        $tplVars = $this->ticketsService->getToDoWidgetHierarchicalAssignments($params);

        $tplVars['hasMoreTickets'] = $this->hasMoreTickets($tplVars['tickets'] ?? [], (int) $params['limit']);
        $tplVars['nextOffset'] = $params['offset'] + $params['limit'];
        $tplVars['isLoadMore'] = true;
        $tplVars['limit'] = $params['limit'];

        return $tplVars;
    }

    /**
     * Computes whether more tickets are available to load by checking whether a
     * full page was returned.
     *
     * @param  array  $ticketGroups  The grouped tickets returned by the tickets service.
     * @param  int  $limit  The current page limit.
     * @return bool True when at least a full page worth of tickets was loaded.
     *
     * @api
     */
    public function hasMoreTickets(array $ticketGroups, int $limit): bool
    {
        $totalLoadedTickets = 0;

        foreach ($ticketGroups as $ticketGroup) {
            $totalLoadedTickets += collect($ticketGroups)->countNested('tickets');
        }

        return $totalLoadedTickets >= $limit;
    }

    /**
     * Reads the user's stored personal task sorting preferences.
     *
     * @param  int  $userId  The user whose sorting to read.
     * @return array The decoded sorting array, or an empty array when none is stored.
     *
     * @api
     */
    public function getUserSorting(int $userId): array
    {
        $sorting = $this->settingsService->getSetting($this->sortingKey($userId));

        if ($sorting) {
            return json_decode($sorting, true) ?? [];
        }

        return [];
    }

    /**
     * Persists the user's personal task sorting preferences and applies any group
     * changes (time/project/priority moves) and dependency (parent) updates.
     *
     * Owns the json_decode / normalize / +10 ordering, persistence of the sorting
     * setting, the dependency updates and the group-change application. Returns the
     * success/error counts of the group changes so the caller can map them to
     * notifications.
     *
     * @param  int  $userId  The user whose sorting to persist.
     * @param  mixed  $rawItems  The raw, JSON-encoded sort items from the request (array expected).
     * @param  array  $groupChanges  The raw, JSON-encoded group-change items from the request.
     * @param  string  $groupBy  The grouping type the changes apply to (time, project, priority).
     * @return array{sorted: bool, successCount: int, errorCount: int} Result of the operation.
     *
     * @api
     */
    public function saveTodoSorting(int $userId, mixed $rawItems, array $groupChanges, string $groupBy): array
    {
        $successCount = 0;
        $errorCount = 0;

        $decodedGroupChanges = [];
        foreach ($groupChanges as $value) {
            $decodedGroupChanges[] = json_decode($value, true);
        }

        if (! empty($decodedGroupChanges)) {
            [$successCount, $errorCount] = $this->processGroupChanges($decodedGroupChanges, $groupBy);
        }

        // The sorting payload must be an array to be persisted; group changes are
        // applied above regardless so they are never lost on a malformed payload.
        if (! is_array($rawItems)) {
            return [
                'sorted' => false,
                'successCount' => $successCount,
                'errorCount' => $errorCount,
            ];
        }

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
        }, $rawItems);

        $this->settingsService->saveSetting($this->sortingKey($userId), json_encode($taskList));
        $this->updateTicketDependencies($taskList);

        return [
            'sorted' => true,
            'successCount' => $successCount,
            'errorCount' => $errorCount,
        ];
    }

    /**
     * Toggles the collapse state of a task for a user and returns the new state.
     *
     * @param  int  $userId  The user whose collapse state to toggle.
     * @param  string  $taskId  The task whose collapse state to toggle.
     * @return string The new collapse state ('open' or 'closed').
     *
     * @api
     */
    public function toggleTaskCollapse(int $userId, string $taskId): string
    {
        $toggleKey = "user.{$userId}.taskCollapsed.{$taskId}";

        $currentState = $this->settingsService->getSetting($toggleKey, 'open');
        $newState = ($currentState === 'open') ? 'closed' : 'open';
        $this->settingsService->saveSetting($toggleKey, $newState);

        return $newState;
    }

    /**
     * Quick-adds a to-do, defaulting its due date based on the group context
     * (this week / overdue / later) when no explicit date was provided.
     *
     * @param  array  $params  The quick-add parameters (must contain 'quickadd').
     * @return array|bool|int The result of the underlying quick-add call.
     *
     * @api
     */
    public function addTodo(array $params): array|bool|int
    {
        $params['dateToFinish'] = $this->resolveQuickAddDueDate($params);

        return $this->ticketsService->quickAddTicket($params);
    }

    /**
     * Creates a subtask under the given parent ticket.
     *
     * @param  array  $values  The subtask form values.
     * @param  int  $parentTicketId  The id of the parent ticket.
     * @return bool True on success, false otherwise.
     *
     * @api
     */
    public function addSubtask(array $values, int $parentTicketId): bool
    {
        $parentTicket = $this->ticketsService->getTicket($parentTicketId);

        return $this->ticketsService->upsertSubtask($values, $parentTicket);
    }

    /**
     * Resolves the due date for a quick-added to-do based on the group context.
     *
     * If a non-empty date was already supplied it is returned unchanged. Otherwise
     * "thisWeek" maps to next Friday, "overdue" maps to today, and "later" leaves
     * the date empty.
     *
     * @param  array  $params  The quick-add parameters.
     * @return string The resolved due date (Y-m-d) or an empty string.
     *
     * @api
     */
    public function resolveQuickAddDueDate(array $params): string
    {
        $dateToFinish = $params['dateToFinish'] ?? '';

        if ($dateToFinish !== '') {
            return $dateToFinish;
        }

        $group = $params['group'] ?? null;

        if ($group === 'thisWeek') {
            // Due this week - set to end of week (Friday)
            return date('Y-m-d', strtotime('next friday'));
        }

        if ($group === 'overdue') {
            // Overdue - set to today
            return date('Y-m-d');
        }

        // For 'later' group (or no group), leave date empty
        return '';
    }

    /**
     * Processes group changes and updates the corresponding task fields.
     *
     * @param  array  $groupChanges  Array of decoded group change data.
     * @param  string  $groupBy  The grouping type (time, project, priority).
     * @return array{0: int, 1: int} The [successCount, errorCount] tuple.
     */
    private function processGroupChanges(array $groupChanges, string $groupBy): array
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

        return [$successCount, $errorCount];
    }

    /**
     * Maps a group key to field updates based on the grouping type.
     *
     * @param  string  $groupBy  The grouping type.
     * @param  string  $groupKey  The target group key.
     * @return array Fields to update.
     */
    public function mapGroupToFields(string $groupBy, string $groupKey): array
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
     * Maps a time group to date fields.
     *
     * @param  string  $groupKey  Time group key (overdue, thisWeek, later).
     * @return array Fields to update.
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
     * Maps a project group to the project field.
     *
     * @param  string  $groupKey  Project ID.
     * @return array Fields to update.
     */
    private function mapProjectGroupToFields(string $groupKey): array
    {
        // Validate that the group key is a valid project ID
        if (is_numeric($groupKey) && $groupKey > 0) {
            return ['projectId' => (int) $groupKey];
        }

        return [];
    }

    /**
     * Maps a priority group to the priority field.
     *
     * @param  string  $groupKey  Priority value.
     * @return array Fields to update.
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
     * Checks whether the current user can update a specific task.
     *
     * @param  int  $taskId  The task ID to check.
     * @return bool True if the user can update, false otherwise.
     */
    private function canUserUpdateTask(int $taskId): bool
    {
        try {
            // Attempt to get the ticket - this will return false if user doesn't have access
            $ticket = $this->ticketsService->getTicket($taskId);

            if (! $ticket || empty($ticket)) {
                return false;
            }

            // If user can view the ticket, they can update it
            return true;
        } catch (\Exception $e) {
            Log::error("Permission check failed for task {$taskId}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Updates ticket dependencies based on the sorting hierarchy.
     *
     * @param  array  $sorting  The sorting data with parent-child relationships.
     */
    public function updateTicketDependencies(array $sorting): void
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

            // For tickets with parents, set the dependingTicketId
            if ($parentId > 0) {
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
     * Builds the per-user settings key for the stored to-do sorting.
     *
     * @param  int  $userId  The user the sorting belongs to.
     * @return string The settings key.
     */
    private function sortingKey(int $userId): string
    {
        return "user.{$userId}.myTodosSorting";
    }
}
