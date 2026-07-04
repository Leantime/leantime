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
 * Create multiple tasks in a single operation.
 */
#[Name('bulkAddTasks')]
#[Description('Creates multiple tasks in one operation. Each element needs headline and projectId at minimum. Optional: description, editorId, userId, dateToFinish, status, editFrom, editTo, milestone.')]
class BulkAddTasksTool extends Tool
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
            'tasks' => $schema->array()
                ->description('Array of task objects. Each must contain headline and projectId.')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $tasks = $request->array('tasks');
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

        return Response::text(
            "Bulk task creation completed. Success: {$successCount}, Failed: {$failureCount}\n\n".
            Str::toMarkdown($results)
        );
    }
}
