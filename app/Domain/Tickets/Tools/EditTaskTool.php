<?php

namespace Leantime\Domain\Tickets\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Core\Exceptions\Contracts\LeantimeExceptionInterface;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Update an existing task.
 */
class EditTaskTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * Get the tool name.
     */
    public function name(): string
    {
        return 'editTask';
    }

    /**
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Updates an existing task as defined by the `id` parameter and using an array `params` where they key is the column name and the value is the value that it should be updated to. Dates need to be provided as iso8601 strings (example: 2024-04-30T15:00:00-04:00). Commonly updated fields are: headline, type, description, projectId, status (a status id from the getStatusLabels tool, a status name, or a status type: new, inprogress, done), dependingTicketId (parent task; omitted fields are never changed), storypoints (often called effort), dateToFinish (due date), planHours. Due dates should not be used as a form of timeboxing since they may represent client due dates. Instead use editFrom and editTo dates to schedule a task for a specific user.';
    }

    /**
     * Define the tool input schema.
     */
    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('id')->description('ID of the task to update.')->required()
            ->raw('params', ['type' => 'object', 'description' => 'Key-value pairs of fields to update. Example: {"headline": "New title", "status": 3}'])->required();
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $id = (int) ($arguments['id'] ?? 0);
        $params = ($arguments['params'] ?? null);

        if (is_array($params) && ! empty($params) && isset($params[0]) && is_array($params[0])) {
            $params = $params[0];
        }

        if (! is_array($params)) {
            return ToolResult::error('The params parameter is not a valid object. Provide key-value pairs.');
        }

        // patchTicket() reports a missing/inaccessible task, a denied edit, an invalid status or
        // a patch with no known field as an error instead of a bare false (#3704).
        try {
            $ignoredFields = $this->ticketsService->getIgnoredPatchFields($params);
            $updated = $this->ticketsService->patchTicket($id, $params);
        } catch (LeantimeExceptionInterface $e) {
            return ToolResult::error($e->getClientMessage());
        }

        if (! $updated) {
            return ToolResult::error('Failed to update task.');
        }

        if ($ignoredFields !== []) {
            return ToolResult::text('Task updated. Ignored unknown fields: '.implode(', ', $ignoredFields).'.');
        }

        return ToolResult::text('Task updated successfully.');
    }
}
