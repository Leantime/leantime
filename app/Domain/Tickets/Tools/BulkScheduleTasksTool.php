<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Schedule multiple tasks by setting editFrom and editTo dates.
 */
#[Name('bulkScheduleTasks')]
#[Description('Schedules multiple tasks by setting editFrom/editTo dates. Enables timeboxing multiple tasks efficiently. All tasks must have at least 15 minutes duration.')]
class BulkScheduleTasksTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'schedules' => $schema->array()
                ->description('Array of schedule objects. Each must have taskId, editFrom (ISO8601), and editTo (ISO8601).')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $schedules = $request->array('schedules');
        $results = [];
        $successCount = 0;
        $failureCount = 0;
        $validationErrors = [];

        foreach ($schedules as $index => $schedule) {
            if (! isset($schedule['taskId']) || ! isset($schedule['editFrom']) || ! isset($schedule['editTo'])) {
                $validationErrors[] = "Schedule #{$index} is missing required fields (taskId, editFrom, editTo)";

                continue;
            }

            try {
                $editFrom = new \DateTime($schedule['editFrom']);
                $editTo = new \DateTime($schedule['editTo']);
            } catch (\Exception $e) {
                $validationErrors[] = "Schedule #{$index} has invalid date format. Use ISO8601 (e.g. 2024-04-30T15:00:00-04:00)";

                continue;
            }

            $duration = $editTo->getTimestamp() - $editFrom->getTimestamp();

            if ($duration < 900) {
                $validationErrors[] = "Schedule #{$index} is shorter than the minimum 15 minute duration";
            }
        }

        if (! empty($validationErrors)) {
            return Response::error("Validation failed:\n- ".implode("\n- ", $validationErrors));
        }

        foreach ($schedules as $schedule) {
            $taskId = $schedule['taskId'];
            $params = [
                'editFrom' => $schedule['editFrom'],
                'editTo' => $schedule['editTo'],
            ];

            if ($this->ticketsService->patch($taskId, $params)) {
                $successCount++;
                $results[] = ['taskId' => $taskId, 'status' => 'success'];
            } else {
                $failureCount++;
                $results[] = ['taskId' => $taskId, 'status' => 'error', 'message' => 'Failed to schedule task'];
            }
        }

        return Response::text(
            "Bulk task scheduling completed. Success: {$successCount}, Failed: {$failureCount}\n\n".
            Str::toMarkdown($results)
        );
    }
}
