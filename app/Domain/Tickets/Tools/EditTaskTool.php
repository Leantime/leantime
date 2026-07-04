<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Update an existing task.
 */
#[Name('editTask')]
#[Description('Updates an existing task. Params is a key-value object of fields to update. Common fields: headline, type, description, projectId, status (use getStatusLabels for IDs), storypoints, dateToFinish (due date, ISO8601), planHours. Use editFrom/editTo for timeboxing, not dateToFinish.')]
class EditTaskTool extends Tool
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
            'id' => $schema->integer()
                ->description('ID of the task to update.')
                ->required(),
            'params' => $schema->object()
                ->description('Key-value pairs of fields to update. Example: {"headline": "New title", "status": 3}')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $id = $request->integer('id');
        $params = $request->get('params');

        if (is_array($params) && ! empty($params) && isset($params[0]) && is_array($params[0])) {
            $params = $params[0];
        }

        if (! is_array($params)) {
            return Response::error('The params parameter is not a valid object. Provide key-value pairs.');
        }

        if ($this->ticketsService->patch($id, $params)) {
            return Response::text('Task updated successfully.');
        }

        return Response::error('Failed to update task.');
    }
}
