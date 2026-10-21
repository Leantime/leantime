<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Update an existing milestone.
 */
#[IsIdempotent]
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
     * Get the tool's display title.
     */
    public function title(): string
    {
        return 'Edit Milestone';
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
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->description('ID of the milestone to update.')
                ->required(),
            'params' => $schema->object()
                ->description('Key-value pairs of fields to update. Example: {"headline": "New title", "editFrom": "2024-04-30T15:00:00-04:00"}')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $id = (int) ($arguments['id'] ?? 0);
        $params = ($arguments['params'] ?? null);

        if (is_array($params) && ! empty($params) && isset($params[0]) && is_array($params[0])) {
            $params = $params[0];
        }

        if (! is_array($params)) {
            return Response::error('The params parameter is not a valid object. Provide key-value pairs.');
        }

        if ($this->ticketsService->patch($id, $params)) {
            return Response::text('Milestone updated successfully.');
        }

        return Response::error('Failed to update milestone.');
    }
}
