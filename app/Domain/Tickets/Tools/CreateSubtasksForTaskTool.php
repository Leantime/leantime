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
 * Create multiple subtasks for a parent task.
 */
#[Name('createSubtasksForTask')]
#[Description('Creates multiple subtasks for a parent task in one operation. Useful for breaking down a large task into smaller components.')]
class CreateSubtasksForTaskTool extends Tool
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
            'parentTaskId' => $schema->integer()
                ->description('ID of the parent task.')
                ->required(),
            'subtasks' => $schema->array()
                ->description('Array of subtask objects. Each needs headline. Optional: description, userId, editFrom, editTo, dateToFinish (all ISO8601), priority (1=Critical to 5=Lowest), planHours, effort (1=XS to 13=XXL).')
                ->required(),
            'projectId' => $schema->integer()
                ->description('Project ID (defaults to parent task\'s project).'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $parentTaskId = $request->integer('parentTaskId');
        $subtasks = $request->array('subtasks');
        $projectId = $request->get('projectId');

        $parentTask = $this->ticketsService->getTicket($parentTaskId);
        if (! $parentTask) {
            return Response::error("Parent task with ID {$parentTaskId} not found.");
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

        return Response::text(
            "Subtask creation completed. Success: {$successCount}, Failed: {$failureCount}\n\n".
            Str::toMarkdown($results)
        );
    }
}
