<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Add a new milestone to a project.
 */
#[IsDestructive(false)]
class AddMilestoneTool extends Tool
{
    public function __construct(
        private Tickets $ticketsService,
    ) {}

    /**
     * Get the tool name.
     */
    public function name(): string
    {
        return 'addMilestone';
    }

    /**
     * Get the tool's display title.
     */
    public function title(): string
    {
        return 'Add Milestone';
    }

    /**
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Adds a new milestone to the project. Milestones are used as hierarchical element to group tasks.';
    }

    /**
     * Define the tool input schema.
     *
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
        $arguments = $request->all();

        $params = [
            'headline' => $arguments['headline'],
            'projectId' => (int) ($arguments['projectId'] ?? 0),
            'editorId' => (int) ($arguments['editorId'] ?? 0),
            'dependentMilestone' => ($arguments['dependentMilestone'] ?? null),
            'tags' => $arguments['color'],
            'editFrom' => $arguments['editFrom'],
            'editTo' => $arguments['editTo'],
            'origin' => 'mcp',
        ];

        $result = $this->ticketsService->quickAddMilestone($params);

        if ($result) {
            return Response::text("Milestone created successfully with ID: {$result}");
        }

        return Response::error('Failed to create milestone.');
    }
}
