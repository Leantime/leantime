<?php

namespace Leantime\Domain\Tickets\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Update an existing milestone.
 */
class EditMilestoneTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * Get the tool name.
     */
    public function name(): string
    {
        return 'editMilestone';
    }

    /**
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Updates an existing milestone as defined by the `id` parameter and using an array `params` where they key is the column name and the value is the value that it should be updated to. Dates need to be provided as iso8601 strings (example: 2024-04-30T15:00:00-04:00). Commonly updated fields are: headline, type, description, projectId, editFrom, editTo, planHours.';
    }

    /**
     * Define the tool input schema.
     */
    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('id')->description('ID of the milestone to update.')->required()
            ->raw('params', ['type' => 'object', 'description' => 'Key-value pairs of fields to update. Example: {"headline": "New title", "editFrom": "2024-04-30T15:00:00-04:00"}'])->required();
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

        if ($this->ticketsService->patch($id, $params)) {
            return ToolResult::text('Milestone updated successfully.');
        }

        return ToolResult::error('Failed to update milestone.');
    }
}
