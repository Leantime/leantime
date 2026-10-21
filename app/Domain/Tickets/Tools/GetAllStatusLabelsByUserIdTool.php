<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Get status labels across all projects for a user.
 */
#[IsReadOnly]
class GetAllStatusLabelsByUserIdTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * Get the tool name.
     */
    public function name(): string
    {
        return 'getAllStatusLabelsByUserId';
    }

    /**
     * Get the tool's display title.
     */
    public function title(): string
    {
        return 'Get All Status Labels By User ID';
    }

    /**
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Get the status labels available for a user across multiple projects This can help get the statuses in a human readable format for a given project.';
    }

    /**
     * Define the tool input schema.
     *
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
        $arguments = $request->all();

        $userId = (int) ($arguments['userId'] ?? 0);
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
