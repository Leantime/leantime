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
 * Add a new milestone to a project.
 */
#[Name('addMilestone')]
#[Description('Adds a new milestone to the project. Milestones are used as hierarchical elements to group tasks.')]
class AddMilestoneTool extends Tool
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
            'headline' => $schema->string()
                ->description('Title of the milestone.')
                ->required(),
            'color' => $schema->string()
                ->description('Choose a color for this milestone using a hex code.')
                ->required(),
            'editFrom' => $schema->string()
                ->description('Start date of the milestone in ISO8601 format (example: 2024-04-30T15:00:00-04:00).')
                ->required(),
            'editTo' => $schema->string()
                ->description('End date of the milestone in ISO8601 format (example: 2024-04-30T15:00:00-04:00).')
                ->required(),
            'projectId' => $schema->integer()
                ->description('Project ID.')
                ->required(),
            'editorId' => $schema->integer()
                ->description('Editor ID.')
                ->required(),
            'dependentMilestone' => $schema->integer()
                ->description('Dependent milestone ID.'),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $params = [
            'headline' => $request->string('headline'),
            'projectId' => $request->integer('projectId'),
            'editorId' => $request->integer('editorId'),
            'dependentMilestone' => $request->get('dependentMilestone'),
            'tags' => $request->string('color'),
            'editFrom' => $request->string('editFrom'),
            'editTo' => $request->string('editTo'),
        ];

        $result = $this->ticketsService->quickAddMilestone($params);

        if ($result) {
            return Response::text("Milestone created successfully with ID: {$result}");
        }

        return Response::error('Failed to create milestone.');
    }
}
