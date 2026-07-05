<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Create multiple tasks in a single operation.
 */
class BulkAddTasksTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * Get the tool name.
     */
    public function name(): string
    {
        return 'bulkAddTasks';
    }

    /**
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Adds multiple task in a single operation. Expects an array of task data where each element contains the same fields as addTicket. Required fields for each ticket: headline, projectId. Optional fields: description, editorId, userId, dateToFinish, status, sprint, editFrom, editTo, milestone.';
    }

    /**
     * Define the tool input schema.
     */
    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->raw('tasks', ['type' => 'array', 'description' => 'Array of task objects. Each must contain headline and projectId.'])->required();
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $tasks = ($arguments['tasks'] ?? []);
        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($tasks as $taskData) {
            try {
                $params = [
                    'headline' => $taskData['headline'] ?? '',
                    'description' => $taskData['description'] ?? '',
                    'projectId' => $taskData['projectId'] ?? null,
                    'editorId' => $taskData['editorId'] ?? null,
                    'userId' => $taskData['userId'] ?? null,
                    'dateToFinish' => $taskData['dateToFinish'] ?? null,
                    'status' => $taskData['status'] ?? 3,
                    'sprint' => $taskData['sprint'] ?? null,
                    'editFrom' => $taskData['editFrom'] ?? null,
                    'editTo' => $taskData['editTo'] ?? null,
                    'milestone' => $taskData['milestone'] ?? null,
                    'type' => 'task',
                ];

                $result = $this->ticketsService->quickAddTicket($params);

                if ($result) {
                    $successCount++;
                    $results[] = ['headline' => $taskData['headline'], 'status' => 'success', 'id' => $result];
                } else {
                    $failureCount++;
                    $results[] = ['headline' => $taskData['headline'], 'status' => 'error', 'message' => 'Failed to create task'];
                }
            } catch (\Exception $e) {
                $failureCount++;
                $results[] = ['headline' => $taskData['headline'] ?? 'Unknown', 'status' => 'error', 'message' => $e->getMessage()];
            }
        }

        return ToolResult::text(
            "Bulk task creation completed. Success: {$successCount}, Failed: {$failureCount}\n\n".
            Str::toMarkdown($results)
        );
    }
}
