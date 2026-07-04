<?php

namespace Leantime\Domain\Calendar\Tools;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Leantime\Domain\Calendar\Services\Calendar;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Create a structured day plan with appropriate events and breaks.
 */
#[Name('scheduleDay')]
#[Description('Creates a structured day plan with appropriate events and breaks. Takes into account existing calendar items and scheduled tasks. Events will be at least 15 minutes long and will be scheduled within working hours. This is ideal for timeboxing a day. Specialist Agent may need the ability to create subtasks to break down larger tasks into multiple working sessions via the createSubtasksForTask tool.')]
class ScheduleDayTool extends Tool
{
    public function __construct(
        private Calendar $calendarService,
        private Tickets $ticketService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'date' => $schema->string()
                ->description('Date to schedule in user timezone in ISO8601 format (example: 2024-04-30T15:00:00-04:00).')
                ->required(),
            'events' => $schema->array()
                ->description('Array of events to schedule. Each should have title, duration (in minutes), and optional priority (1-20).')
                ->required(),
            'workingHoursStart' => $schema->string()
                ->description('Start of working hours in 24-hour format (HH:MM), if none available use 09:00.'),
            'workingHoursEnd' => $schema->string()
                ->description('End of working hours in 24-hour format (HH:MM), if none available use 18:00.'),
            'taskIds' => $schema->array()
                ->description('Array of task IDs to schedule instead of creating events. If provided, these tasks will be scheduled rather than creating new events.'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $date = $request->string('date');
        $events = $request->array('events');
        $workingHoursStart = $request->string('workingHoursStart', '');
        $workingHoursEnd = $request->string('workingHoursEnd', '');
        $taskIds = $request->array('taskIds');

        $dateObj = dtHelper()->parseUserDateTime($date);
        $dateFrom = $dateObj->startOfDay();
        $dateTo = $dateObj->endOfDay();

        $existingEvents = $this->calendarService->getCalendar(session('userdata.id'), $dateFrom, $dateTo);
        $existingTasks = $this->ticketService->getScheduledTasks($dateFrom, $dateTo, session('userdata.id'));

        if ($workingHoursStart !== '' && ($timeparts = explode(':', $workingHoursStart))) {
            $workStart = $dateObj->setTime($timeparts[0], $timeparts[1]);
        } else {
            $workStart = $dateObj->setTime(9, 0);
        }

        if ($workingHoursEnd !== '' && ($timeparts = explode(':', $workingHoursEnd))) {
            $workEnd = $dateObj->setTime($timeparts[0], $timeparts[1]);
        } else {
            $workEnd = $dateObj->setTime(18, 0);
        }

        $availableSlots = $this->findAvailableTimeSlots($workStart, $workEnd, $existingEvents, $existingTasks);

        if (! empty($taskIds)) {
            $tasksToSchedule = [];

            foreach ($taskIds as $taskId) {
                $task = $this->ticketService->getTicket($taskId);
                if ($task) {
                    $tasksToSchedule[] = [
                        'id' => $taskId,
                        'title' => $task->headline ?? 'Untitled Task',
                        'duration' => $events[array_search($taskId, array_column($events, 'taskId'))]['duration'] ?? 30,
                        'priority' => $events[array_search($taskId, array_column($events, 'taskId'))]['priority'] ?? 3,
                    ];
                }
            }

            $scheduledTasks = $this->scheduleItemsInSlots($availableSlots, $tasksToSchedule);

            $successCount = 0;
            $failureCount = 0;
            $results = [];

            foreach ($scheduledTasks as $task) {
                $editFrom = $task['dateFrom'] instanceof CarbonImmutable ? $task['dateFrom']->toIso8601String() : (string) $task['dateFrom'];
                $editTo = $task['dateTo'] instanceof CarbonImmutable ? $task['dateTo']->toIso8601String() : (string) $task['dateTo'];

                if ($this->ticketService->patch($task['id'], ['editFrom' => $editFrom, 'editTo' => $editTo])) {
                    $successCount++;
                    $results[] = ['taskId' => $task['id'], 'status' => 'success'];
                } else {
                    $failureCount++;
                    $results[] = ['taskId' => $task['id'], 'status' => 'error', 'message' => 'Failed to schedule task'];
                }
            }

            return Response::text("Day scheduling completed for {$date}.\n\nTask scheduling: Success: {$successCount}, Failed: {$failureCount}");
        }

        $scheduledEvents = $this->scheduleItemsInSlots($availableSlots, $events);

        $successCount = 0;
        $failureCount = 0;

        foreach ($scheduledEvents as $eventData) {
            $eventDateFrom = $eventData['dateFrom'] instanceof CarbonImmutable ? $eventData['dateFrom']->toIso8601String() : (string) $eventData['dateFrom'];
            $eventDateTo = $eventData['dateTo'] instanceof CarbonImmutable ? $eventData['dateTo']->toIso8601String() : (string) $eventData['dateTo'];

            $result = $this->calendarService->addEvent([
                'description' => $eventData['title'] ?? $eventData['eventTitle'] ?? 'Untitled',
                'dateFrom' => $eventDateFrom,
                'dateTo' => $eventDateTo,
                'allDay' => false,
                'userId' => session('userdata.id'),
            ]);

            if ($result) {
                $successCount++;
            } else {
                $failureCount++;
            }
        }

        return Response::text("Day scheduling completed for {$date}.\n\nEvent creation: Success: {$successCount}, Failed: {$failureCount}");
    }

    /**
     * Find available time slots in a day.
     *
     * @param  CarbonImmutable  $workStart  Start of working hours
     * @param  CarbonImmutable  $workEnd  End of working hours
     * @param  array  $existingEvents  Existing calendar events
     * @param  array  $existingTasks  Existing scheduled tasks
     * @return array Available time slots as [start, end] pairs
     */
    private function findAvailableTimeSlots(CarbonImmutable $workStart, CarbonImmutable $workEnd, array $existingEvents, array $existingTasks): array
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
     * Schedule items (events or tasks) in available time slots.
     *
     * @param  array  $availableSlots  Available time slots
     * @param  array  $items  Items to schedule
     * @return array Scheduled items with dateFrom and dateTo
     */
    private function scheduleItemsInSlots(array $availableSlots, array $items): array
    {
        usort($items, function ($a, $b) {
            $priorityA = $a['priority'] ?? 3;
            $priorityB = $b['priority'] ?? 3;

            if ($priorityA !== $priorityB) {
                return $priorityB - $priorityA;
            }

            $durationA = $a['duration'] ?? 30;
            $durationB = $b['duration'] ?? 30;

            return $durationB - $durationA;
        });

        $scheduledItems = [];

        foreach ($items as $item) {
            $title = $item['title'] ?? $item['eventTitle'] ?? 'Untitled';
            $durationMinutes = $item['duration'] ?? 30;
            $durationSeconds = $durationMinutes * 60;

            foreach ($availableSlots as $key => $slot) {
                $slotDuration = $slot['end']->getTimestamp() - $slot['start']->getTimestamp();

                if ($slotDuration >= $durationSeconds) {
                    $itemStart = $slot['start'];
                    $itemEnd = $itemStart->modify("+{$durationMinutes} minutes");

                    $scheduledItem = [
                        'title' => $title,
                        'dateFrom' => $itemStart,
                        'dateTo' => $itemEnd,
                    ];

                    if (isset($item['id'])) {
                        $scheduledItem['id'] = $item['id'];
                    }

                    $scheduledItems[] = $scheduledItem;

                    $availableSlots[$key]['start'] = $itemEnd;

                    if ($availableSlots[$key]['end']->getTimestamp() - $availableSlots[$key]['start']->getTimestamp() < 900) {
                        unset($availableSlots[$key]);
                    }

                    break;
                }
            }
        }

        return $scheduledItems;
    }
}
