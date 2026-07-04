<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Get status labels for a project.
 */
#[Name('getStatusLabels')]
#[Description('Get status labels for a project. Returns status IDs with names, types (INPROGRESS, DONE, NEW), and kanban availability. Each project can define custom statuses.')]
#[IsReadOnly]
class GetStatusLabelsTool extends Tool
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
        $projectId = $request->integer('projectId');
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
