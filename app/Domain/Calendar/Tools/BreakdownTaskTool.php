<?php

namespace Leantime\Domain\Calendar\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Calendar\Services\Calendar;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Break down a task into multiple subtasks and schedule them.
 */
class BreakdownTaskTool extends Tool
{
    public function __construct(
        private Calendar $calendarService,
        private Tickets $ticketService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('taskId')->description('ID of the task to break down.')
            ->required()
            ->raw('subtasks', ['type' => 'array', 'description' => 'Array of subtask definitions. Each should have title, description, and duration (in minutes).'])->required()
            ->string('startDate')->description('Start date to begin scheduling from in user timezone format ISO8601 (example: 2024-04-30T15:00:00-04:00).')
            ->required()
            ->string('endDate')->description('End date to finish scheduling by in user timezone format ISO8601 (example: 2024-04-30T15:00:00-04:00).');
    }

    public function name(): string
    {
        return 'breakdownTask';
    }

    public function description(): string
    {
        return 'Breaks a task down into subtasks and schedules them.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $taskId = (int) ($arguments['taskId'] ?? 0);
        $subtasks = ($arguments['subtasks'] ?? []);
        $startDate = $arguments['startDate'];
        $endDate = ($arguments['endDate'] ?? null);

        $task = $this->ticketService->getTicket($taskId);

        if (! $task) {
            return ToolResult::error("Task with ID {$taskId} not found.");
        }

        // Create subtasks
        $subtaskResults = [];
        $subtaskIds = [];
        $subtaskDurations = [];

        foreach ($subtasks as $subtaskDef) {
            $params = [
                'headline' => $subtaskDef['title'],
                'description' => $subtaskDef['description'] ?? '',
                'projectId' => $task->projectId,
                'editorId' => session('userdata.id'),
                'userId' => session('userdata.id'),
                'type' => 'subtask',
                'dependingTicketId' => $taskId,
                'status' => 3,
            ];

            $result = $this->ticketService->quickAddTicket($params);

            if ($result) {
                $subtaskResults[] = [
                    'headline' => $subtaskDef['title'],
                    'status' => 'success',
                    'id' => $result,
                ];
                $subtaskIds[] = $result;
                $subtaskDurations[$result] = $subtaskDef['duration'] ?? 30;
            } else {
                $subtaskResults[] = [
                    'headline' => $subtaskDef['title'],
                    'status' => 'error',
                    'message' => 'Failed to create subtask',
                ];
            }
        }

        $createResult = Str::toMarkdown($subtaskResults);

        // Schedule subtasks if we have IDs and a date range
        if (! empty($subtaskIds) && ! empty($startDate)) {
            $currentDate = dtHelper()->parseUserDateTime($startDate);
            $lastDate = $endDate ? dtHelper()->parseUserDateTime($endDate) : $currentDate->addDays(7);

            $scheduledResults = [];

            while ($currentDate <= $lastDate && ! empty($subtaskIds)) {
                $workStart = $currentDate->setTime(9, 0);
                $workEnd = $currentDate->setTime(18, 0);

                $dayFrom = $currentDate->startOfDay();
                $dayTo = $currentDate->endOfDay();
                $existingEvents = $this->calendarService->getCalendar(session('userdata.id'), $dayFrom, $dayTo);
                $existingTasks = $this->ticketService->getScheduledTasks($dayFrom, $dayTo, session('userdata.id'));

                $availableSlots = $this->findAvailableTimeSlots($workStart, $workEnd, $existingEvents, $existingTasks);

                $tasksToSchedule = [];
                foreach ($subtaskIds as $id) {
                    $tasksToSchedule[] = [
                        'id' => $id,
                        'duration' => $subtaskDurations[$id] ?? 30,
                    ];
                }

                $scheduledTasks = $this->scheduleTasksInSlots($availableSlots, $tasksToSchedule);

                foreach ($scheduledTasks as $scheduledTask) {
                    $editFrom = is_object($scheduledTask['dateFrom']) ? $scheduledTask['dateFrom']->toIso8601String() : (string) $scheduledTask['dateFrom'];
                    $editTo = is_object($scheduledTask['dateTo']) ? $scheduledTask['dateTo']->toIso8601String() : (string) $scheduledTask['dateTo'];

                    $this->ticketService->patch($scheduledTask['id'], [
                        'editFrom' => $editFrom,
                        'editTo' => $editTo,
                    ]);

                    $index = array_search($scheduledTask['id'], $subtaskIds);
                    if ($index !== false) {
                        unset($subtaskIds[$index]);
                        $subtaskIds = array_values($subtaskIds);
                    }
                }

                if (! empty($scheduledTasks)) {
                    $dateStr = $currentDate->format('Y-m-d');
                    $scheduledResults[] = 'Scheduled '.count($scheduledTasks)." subtask(s) on {$dateStr}";
                }

                $currentDate = $currentDate->addDay();
            }

            $schedulingSummary = implode("\n", $scheduledResults);

            return ToolResult::text("Task breakdown and scheduling completed.\n\nSubtask Creation:\n{$createResult}\n\nScheduling:\n{$schedulingSummary}");
        }

        return ToolResult::text("Task breakdown initiated.\n\n{$createResult}");
    }

    /**
     * Find available time slots in a day.
     */
    private function findAvailableTimeSlots($workStart, $workEnd, array $existingEvents, array $existingTasks): array
    {
        $busyTimes = [];

        foreach ($existingEvents as $event) {
            $busyTimes[] = [
                'start' => dtHelper()->parseDbDateTime($event['dateFrom']),
                'end' => dtHelper()->parseDbDateTime($event['dateTo']),
            ];
        }

        if (isset($existingTasks['totalTasks'])) {
            foreach ($existingTasks['totalTasks'] as $task) {
                if (! empty($task['editFrom']) && ! empty($task['editTo'])) {
                    $busyTimes[] = [
                        'start' => dtHelper()->parseDbDateTime($task['editFrom']),
                        'end' => dtHelper()->parseDbDateTime($task['editTo']),
                    ];
                }
            }
        }

        usort($busyTimes, function ($a, $b) {
            return $a['start']->getTimestamp() - $b['start']->getTimestamp();
        });

        $mergedBusyTimes = [];
        foreach ($busyTimes as $busy) {
            if (empty($mergedBusyTimes)) {
                $mergedBusyTimes[] = $busy;

                continue;
            }

            $lastBusy = &$mergedBusyTimes[count($mergedBusyTimes) - 1];

            if ($busy['start'] <= $lastBusy['end']) {
                if ($busy['end'] > $lastBusy['end']) {
                    $lastBusy['end'] = $busy['end'];
                }
            } else {
                $mergedBusyTimes[] = $busy;
            }
        }

        $availableSlots = [];
        $currentTime = clone $workStart;

        foreach ($mergedBusyTimes as $busy) {
            if ($busy['end'] <= $workStart || $busy['start'] >= $workEnd) {
                continue;
            }

            $busyStart = max($busy['start'], $workStart);
            $busyEnd = min($busy['end'], $workEnd);

            if ($currentTime < $busyStart) {
                $availableSlots[] = [
                    'start' => clone $currentTime,
                    'end' => clone $busyStart,
                ];
            }

            $currentTime = clone $busyEnd;
        }

        if ($currentTime < $workEnd) {
            $availableSlots[] = [
                'start' => clone $currentTime,
                'end' => clone $workEnd,
            ];
        }

        return $availableSlots;
    }

    /**
     * Schedule tasks in available time slots.
     */
    private function scheduleTasksInSlots(array $availableSlots, array $tasks): array
    {
        usort($tasks, function ($a, $b) {
            $priorityA = $a['priority'] ?? 3;
            $priorityB = $b['priority'] ?? 3;

            if ($priorityA !== $priorityB) {
                return $priorityB - $priorityA;
            }

            $durationA = $a['duration'] ?? 30;
            $durationB = $b['duration'] ?? 30;

            return $durationB - $durationA;
        });

        $scheduledTasks = [];

        foreach ($tasks as $task) {
            $durationMinutes = $task['duration'] ?? 30;
            $durationSeconds = $durationMinutes * 60;
            $taskId = $task['id'];

            foreach ($availableSlots as $key => $slot) {
                $slotDuration = $slot['end']->getTimestamp() - $slot['start']->getTimestamp();

                if ($slotDuration >= $durationSeconds) {
                    $taskStart = clone $slot['start'];
                    $taskEnd = clone $taskStart;
                    $taskEnd = $taskEnd->modify("+{$durationMinutes} minutes");

                    $scheduledTasks[] = [
                        'id' => $taskId,
                        'dateFrom' => $taskStart,
                        'dateTo' => $taskEnd,
                    ];

                    $availableSlots[$key]['start'] = $taskEnd;

                    if ($availableSlots[$key]['end']->getTimestamp() - $availableSlots[$key]['start']->getTimestamp() < 900) {
                        unset($availableSlots[$key]);
                    }

                    break;
                }
            }
        }

        return $scheduledTasks;
    }
}
