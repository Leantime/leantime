<?php

namespace Leantime\Domain\Calendar\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Calendar\Services\Calendar;

/**
 * Update multiple calendar events in a single operation.
 */
class BulkEditCalendarEventsTool extends Tool
{
    public function __construct(
        private Calendar $calendarService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->raw('updates', ['type' => 'array', 'description' => 'Array of updates. Each element must have id and the fields to update.'])->required();
    }

    public function name(): string
    {
        return 'bulkEditEvents';
    }

    public function description(): string
    {
        return 'Updates multiple calendar events in a single operation.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $updates = ($arguments['updates'] ?? []);
        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($updates as $update) {
            if (! isset($update['id'])) {
                $failureCount++;
                $results[] = ['status' => 'error', 'message' => 'Missing event ID'];

                continue;
            }

            if (isset($update['dateFrom']) && isset($update['dateTo'])) {
                $dateFrom = dtHelper()->parseUserDateTime($update['dateFrom']);
                $dateTo = dtHelper()->parseUserDateTime($update['dateTo']);

                if ($dateFrom->format('Y-m-d') !== $dateTo->format('Y-m-d')) {
                    $failureCount++;
                    $results[] = [
                        'id' => $update['id'],
                        'status' => 'error',
                        'message' => 'Event must start and end on the same day',
                    ];

                    continue;
                }

                $duration = $dateTo->getTimestamp() - $dateFrom->getTimestamp();
                if ($duration < 900) {
                    $failureCount++;
                    $results[] = [
                        'id' => $update['id'],
                        'status' => 'error',
                        'message' => 'Event must be at least 15 minutes long',
                    ];

                    continue;
                }
            }

            $eventData = [
                'id' => $update['id'],
                'description' => $update['eventTitle'] ?? null,
                'dateFrom' => $update['dateFrom'] ?? null,
                'dateTo' => $update['dateTo'] ?? null,
                'allDay' => $update['allDay'] ?? null,
            ];

            $eventData = array_filter($eventData, function ($value) {
                return $value !== null;
            });

            if ($this->calendarService->editEvent($eventData)) {
                $successCount++;
                $results[] = ['id' => $update['id'], 'status' => 'success'];
            } else {
                $failureCount++;
                $results[] = ['id' => $update['id'], 'status' => 'error', 'message' => 'Failed to update event'];
            }
        }

        return ToolResult::text(
            "Bulk event update completed. Success: {$successCount}, Failed: {$failureCount}\n\n".
            Str::toMarkdown($results)
        );
    }
}
