<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Leantime\Core\Exceptions\Contracts\LeantimeExceptionInterface;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Create multiple subtasks for a parent task.
 */
#[IsDestructive(false)]
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
     * Get the tool's display title.
     */
    public function title(): string
    {
        return 'Create Subtasks For Task';
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
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'parentTaskId' => $schema->integer()
                ->description('ID of the parent task.')
                ->required(),
            'subtasks' => $schema->array()->items($schema->object())
                ->description('Array of subtask objects. Each needs headline. Optional: description, status (id, name or type: new/inprogress/done; defaults to the project NEW status), userId, editFrom, editTo, dateToFinish (all ISO8601), priority (1=Critical to 5=Lowest), planHours, effort (1=XS to 13=XXL).')
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
        $arguments = $request->all();

        $parentTaskId = (int) ($arguments['parentTaskId'] ?? 0);
        $subtasks = ($arguments['subtasks'] ?? []);
        $projectId = ($arguments['projectId'] ?? null);

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
                'status' => $subtaskData['status'] ?? null,
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

            try {
                $params['origin'] = 'mcp';
                $result = $this->ticketsService->quickAddTicket($params);
            } catch (LeantimeExceptionInterface $e) {
                $failureCount++;
                $results[] = ['headline' => $subtaskData['headline'] ?? 'Unknown', 'status' => 'error', 'message' => $e->getClientMessage()];

                continue;
            }

            if ($result && ! is_array($result)) {
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
