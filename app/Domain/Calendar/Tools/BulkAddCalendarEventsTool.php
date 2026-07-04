<?php

namespace Leantime\Domain\Calendar\Tools;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Leantime\Domain\Calendar\Services\Calendar;

/**
 * Add multiple calendar events in a single operation.
 */
#[Name('bulkAddEvents')]
#[Description('Adds multiple calendar events in a single operation. All events must be for the same day to prevent scheduling across multiple days. Each event must be at least 15 minutes long. Expects an array of event data where each element contains eventTitle, dateFrom, dateTo, and optional allDay flag.')]
class BulkAddCalendarEventsTool extends Tool
{
    public function __construct(
        private Calendar $calendarService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'events' => $schema->array()
                ->description('Array of event data. Each element should contain eventTitle, dateFrom, dateTo, and optional allDay.')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $events = $request->array('events');
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

            $dateFrom = ($eventData['dateFrom'] instanceof CarbonImmutable) ? $eventData['dateFrom'] : dtHelper()->parseUserDateTime($eventData['dateFrom']);
            $dateTo = ($eventData['dateTo'] instanceof CarbonImmutable) ? $eventData['dateTo'] : dtHelper()->parseUserDateTime($eventData['dateTo']);
        }

        if (! empty($validationErrors)) {
            return Response::error("Validation failed:\n- ".implode("\n- ", $validationErrors));
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
                    'message' => $e->getMessage(),
                ];
                Log::error($e);
            }
        }

        return Response::text(
            "Bulk event creation completed. Success: {$successCount}, Failed: {$failureCount}\n\n".
            Str::toMarkdown($results)
        );
    }
}
