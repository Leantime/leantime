<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Get status labels for a project.
 */
#[IsReadOnly]
class GetStatusLabelsTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * Get the tool name.
     */
    public function name(): string
    {
        return 'getStatusLabels';
    }

    /**
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Get the status labels available for a project. This can help get the statuses in a human readable format for a given project, since the database only stores the status id and each project can define its own statuses. The array is keyed by the status id and returns an array with name (language string), class (css class), statusType (INPROGRESS, DONE, NEW) and whether its available in a kanbanCol (true/false).';
    }

    /**
     * Define the tool input schema.
     */
    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('projectId')->description('Project ID to get status labels for.')->required();
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $projectId = (int) ($arguments['projectId'] ?? 0);
        $status = $this->ticketsService->getStatusLabels($projectId);

        $statusAIString = '## Status Labels';
        foreach ($status as $key => $value) {
            $result = [
                'id' => $key,
                'name' => $value['name'],
                'statusType' => $value['statusType'],
                'isKanbanColumn' => $value['kanbanCol'] === '1' ? 'yes' : 'no',
            ];

            $statusAIString .= Str::toMarkdown($result)."\n";
        }

        return ToolResult::text($statusAIString);
    }
}
