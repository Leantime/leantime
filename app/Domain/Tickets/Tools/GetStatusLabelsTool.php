<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Get status labels for a project.
 */
#[IsReadOnly]
class GetStatusLabelsTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
        private Projects $projectService,
    ) {}

    /**
     * Get the tool name.
     */
    public function name(): string
    {
        return 'getStatusLabels';
    }

    /**
     * Get the tool's display title.
     */
    public function title(): string
    {
        return 'Get Status Labels';
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
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'projectId' => $schema->integer()
                ->description('Project ID to get status labels for.')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $projectId = (int) ($arguments['projectId'] ?? 0);

        // Only for a project the caller can view: getProject() returns false otherwise, so a
        // foreign project id must not expose its status configuration.
        if ($projectId <= 0 || ! $this->projectService->getProject($projectId)) {
            return Response::error("Project {$projectId} was not found or you do not have access to it.");
        }

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

        return Response::text($statusAIString);
    }
}
