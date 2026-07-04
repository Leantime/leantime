<?php

namespace Leantime\Domain\Calendar\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Leantime\Domain\Calendar\Services\Calendar;

/**
 * Update multiple calendar events in a single operation.
 */
#[Name('bulkEditEvents')]
#[Description('Updates multiple calendar events in a single operation. All events must maintain a minimum duration of 15 minutes and remain on the same day. Expects an array where each element contains id and the fields to update (eventTitle, dateFrom, dateTo, allDay).')]
class BulkEditCalendarEventsTool extends Tool
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
            'updates' => $schema->array()
                ->description('Array of updates. Each element must have id and the fields to update.')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $updates = $request->array('updates');
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

        return Response::text(
            "Bulk event update completed. Success: {$successCount}, Failed: {$failureCount}\n\n".
            Str::toMarkdown($results)
        );
    }
}
