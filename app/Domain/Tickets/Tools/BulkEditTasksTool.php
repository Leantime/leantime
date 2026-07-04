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
 * Update multiple tasks in a single operation.
 */
#[Name('bulkEditTasks')]
#[Description('Updates multiple tasks in one operation. Each element must have id and fields to update. Fields: headline, description, projectId, editorId, userId, dateToFinish, status, editFrom, editTo, milestoneId.')]
class BulkEditTasksTool extends Tool
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
            'updates' => $schema->array()
                ->description('Array of update objects. Each must have id and the fields to update.')
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
                $results[] = ['status' => 'error', 'message' => 'Missing task ID'];

                continue;
            }

            $id = $update['id'];
            unset($update['id']);

            if ($this->ticketsService->patch($id, $update)) {
                $successCount++;
                $results[] = ['id' => $id, 'status' => 'success'];
            } else {
                $failureCount++;
                $results[] = ['id' => $id, 'status' => 'error', 'message' => 'Failed to update task'];
            }
        }

        return Response::text(
            "Bulk task update completed. Success: {$successCount}, Failed: {$failureCount}\n\n".
            Str::toMarkdown($results)
        );
    }
}
