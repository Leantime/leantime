<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Update multiple tasks in a single operation.
 */
class BulkEditTasksTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * Get the tool name.
     */
    public function name(): string
    {
        return 'bulkEditTasks';
    }

    /**
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Updates multiple tasks in a single operation. Expects an array where each element contains ticketId and the fields to update. Field names that can be updated are: headline, description, projectId, editorId, userId, dateToFinish, status, editFrom, editTo, milestoneId';
    }

    /**
     * Define the tool input schema.
     */
    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->raw('updates', ['type' => 'array', 'description' => 'Array of update objects. Each must have id and the fields to update.'])->required();
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $updates = ($arguments['updates'] ?? []);
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

        return ToolResult::text(
            "Bulk task update completed. Success: {$successCount}, Failed: {$failureCount}\n\n".
            Str::toMarkdown($results)
        );
    }
}
