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
 * Get status labels across all projects for a user.
 */
#[Name('getAllStatusLabelsByUserId')]
#[Description('Get status labels across all projects for a user. Returns human-readable status information grouped by project.')]
#[IsReadOnly]
class GetAllStatusLabelsByUserIdTool extends Tool
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
            'userId' => $schema->integer()
                ->description('User ID to get status labels for.')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $userId = $request->integer('userId');
        if ($userId === 0) {
            $userId = session('userdata.id');
        }

        $status = $this->ticketsService->getAllStatusLabelsByUserId($userId);

        $statusAIString = '## Status Labels';
        foreach ($status as $projectKey => $projectStatus) {
            foreach ($projectStatus as $key => $value) {
                $result = [
                    'id' => $key,
                    'projectId' => $projectKey,
                    'name' => $value['name'],
                    'statusType' => $value['statusType'],
                    'isKanbanColumn' => $value['kanbanCol'] === '1' ? 'yes' : 'no',
                ];

                $statusAIString .= Str::toMarkdown($result)."\n";
            }
        }

        return Response::text($statusAIString);
    }
}
