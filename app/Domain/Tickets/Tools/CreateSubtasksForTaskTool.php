<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Create multiple subtasks for a parent task.
 */
class CreateSubtasksForTaskTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * Get the tool name.
     */
    public function name(): string
    {
        return 'createSubtasksForTask';
    }

    /**
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Creates multiple subtasks for a parent task in a single operation. This is useful for breaking down a large task into smaller components. Optionally schedules these subtasks with editFrom/editTo dates. Expects a parent task ID and an array of subtask definitions.';
    }

    /**
     * Define the tool input schema.
     */
    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('parentTaskId')->description('ID of the parent task.')->required()
            ->raw('subtasks', ['type' => 'array', 'description' => 'Array of subtask objects. Each needs headline. Optional: description, userId, editFrom, editTo, dateToFinish (all ISO8601), priority (1=Critical to 5=Lowest), planHours, effort (1=XS to 13=XXL).'])->required()
            ->integer('projectId')->description('Project ID (defaults to parent task\'s project).');
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $parentTaskId = (int) ($arguments['parentTaskId'] ?? 0);
        $subtasks = ($arguments['subtasks'] ?? []);
        $projectId = ($arguments['projectId'] ?? null);

        $parentTask = $this->ticketsService->getTicket($parentTaskId);
        if (! $parentTask) {
            return ToolResult::error("Parent task with ID {$parentTaskId} not found.");
        }

        if ($projectId === null) {
            $projectId = $parentTask->projectId;
        }

        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($subtasks as $subtaskData) {
            $params = [
                'headline' => $subtaskData['headline'] ?? '',
                'description' => $subtaskData['description'] ?? '',
                'projectId' => $projectId,
                'editorId' => null,
                'userId' => $subtaskData['userId'] ?? session('userdata.id'),
                'dateToFinish' => $subtaskData['dateToFinish'] ?? null,
                'status' => $subtaskData['status'] ?? 3,
                'sprint' => null,
                'editFrom' => $subtaskData['editFrom'] ?? null,
                'editTo' => $subtaskData['editTo'] ?? null,
                'milestone' => null,
                'type' => 'subtask',
                'dependingTicketId' => $parentTaskId,
                'storypoints' => $subtaskData['effort'] ?? 2,
                'priority' => $subtaskData['priority'] ?? 3,
                'planHours' => $subtaskData['planHours'] ?? 1,
            ];

            $result = $this->ticketsService->quickAddTicket($params);

            if ($result) {
                $successCount++;
                $results[] = ['headline' => $subtaskData['headline'], 'status' => 'success', 'id' => $result];
            } else {
                $failureCount++;
                $results[] = ['headline' => $subtaskData['headline'] ?? 'Unknown', 'status' => 'error', 'message' => 'Failed to create subtask'];
            }
        }

        return ToolResult::text(
            "Subtask creation completed. Success: {$successCount}, Failed: {$failureCount}\n\n".
            Str::toMarkdown($results)
        );
    }
}
