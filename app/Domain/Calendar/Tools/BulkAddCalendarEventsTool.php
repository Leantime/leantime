<?php

namespace Leantime\Domain\Calendar\Tools;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Calendar\Services\Calendar;

/**
 * Add multiple calendar events in a single operation.
 */
class BulkAddCalendarEventsTool extends Tool
{
    public function __construct(
        private Calendar $calendarService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->raw('events', ['type' => 'array', 'description' => 'Array of event data. Each element should contain eventTitle, dateFrom, dateTo, and optional allDay.'])->required();
    }

    public function name(): string
    {
        return 'bulkAddEvents';
    }

    public function description(): string
    {
        return 'Adds multiple calendar events in a single operation.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $events = ($arguments['events'] ?? []);
        $results = [];
        $successCount = 0;
        $failureCount = 0;

        $validationErrors = [];

        foreach ($events as $index => $eventData) {
            if (! isset($eventData['eventTitle']) || ! isset($eventData['dateFrom']) || ! isset($eventData['dateTo'])) {
                $validationErrors[] = "Event #{$index} is missing required fields (eventTitle, dateFrom, dateTo)";
                Log::error("Event #{$index} is missing required fields (eventTitle, dateFrom, dateTo)");

                continue;
            }

            try {
                $dateFrom = ($eventData['dateFrom'] instanceof CarbonImmutable) ? $eventData['dateFrom'] : dtHelper()->parseUserDateTime($eventData['dateFrom']);
                $dateTo = ($eventData['dateTo'] instanceof CarbonImmutable) ? $eventData['dateTo'] : dtHelper()->parseUserDateTime($eventData['dateTo']);

                $durationSeconds = $dateTo->getTimestamp() - $dateFrom->getTimestamp();
                if ($durationSeconds < 900) {
                    $validationErrors[] = "Event #{$index} is shorter than the minimum 15 minute duration";
                }
            } catch (\Exception $e) {
                $validationErrors[] = "Event #{$index} has invalid date format";
            }
        }

        if (! empty($validationErrors)) {
            return ToolResult::error("Validation failed:\n- ".implode("\n- ", $validationErrors));
        }

        foreach ($events as $eventData) {
            try {
                $result = $this->calendarService->addEvent([
                    'description' => $eventData['eventTitle'],
                    'dateFrom' => $eventData['dateFrom'],
                    'dateTo' => $eventData['dateTo'],
                    'allDay' => $eventData['allDay'] ?? false,
                    'userId' => session('userdata.id'),
                ]);

                if ($result) {
                    $successCount++;
                    $results[] = [
                        'title' => $eventData['eventTitle'],
                        'status' => 'success',
                        'id' => $result,
                    ];
                } else {
                    $failureCount++;
                    $results[] = [
                        'title' => $eventData['eventTitle'],
                        'status' => 'error',
                        'message' => 'Failed to create event',
                    ];
                }
            } catch (\Exception $e) {
                $failureCount++;
                $results[] = [
                    'title' => $eventData['eventTitle'] ?? 'Unknown',
                    'status' => 'error',
                    'message' => 'Failed to create event',
                ];
                Log::error($e);
            }
        }

        return ToolResult::text(
            "Bulk event creation completed. Success: {$successCount}, Failed: {$failureCount}\n\n".
            Str::toMarkdown($results)
        );
    }
}
