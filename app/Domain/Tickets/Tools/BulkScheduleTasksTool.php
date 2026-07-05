<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Schedule multiple tasks by setting editFrom and editTo dates.
 */
class BulkScheduleTasksTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * Get the tool name.
     */
    public function name(): string
    {
        return 'bulkScheduleTasks';
    }

    /**
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Schedules multiple tasks by setting editFrom and editTo dates in a single operation. This allows timeboxing multiple tasks efficiently. All tasks must have at least 15 minutes duration. Expects an array where each element contains taskId, editFrom, and editTo.';
    }

    /**
     * Define the tool input schema.
     */
    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->raw('schedules', ['type' => 'array', 'description' => 'Array of schedule objects. Each must have taskId, editFrom (ISO8601), and editTo (ISO8601).'])->required();
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $schedules = ($arguments['schedules'] ?? []);
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
            return ToolResult::error("Validation failed:\n- ".implode("\n- ", $validationErrors));
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

        return ToolResult::text(
            "Bulk task scheduling completed. Success: {$successCount}, Failed: {$failureCount}\n\n".
            Str::toMarkdown($results)
        );
    }
}
