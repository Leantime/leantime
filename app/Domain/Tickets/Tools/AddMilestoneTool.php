<?php

namespace Leantime\Domain\Tickets\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Add a new milestone to a project.
 */
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
     * Get the tool description.
     */
    public function description(): string
    {
        return 'Adds a new milestone to the project. Milestones are used as hierarchical element to group tasks.';
    }

    /**
     * Define the tool input schema.
     */
    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('headline')->description('Title of the milestone.')->required()
            ->string('color')->description('Choose a color for this milestone using a hex code.')->required()
            ->string('editFrom')->description('Start date of the milestone in ISO8601 format (example: 2024-04-30T15:00:00-04:00).')->required()
            ->string('editTo')->description('End date of the milestone in ISO8601 format (example: 2024-04-30T15:00:00-04:00).')->required()
            ->integer('projectId')->description('Project ID.')->required()
            ->integer('editorId')->description('Editor ID.')->required()
            ->integer('dependentMilestone')->description('Dependent milestone ID.');
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $params = [
            'headline' => $arguments['headline'],
            'projectId' => (int) ($arguments['projectId'] ?? 0),
            'editorId' => (int) ($arguments['editorId'] ?? 0),
            'dependentMilestone' => ($arguments['dependentMilestone'] ?? null),
            'tags' => $arguments['color'],
            'editFrom' => $arguments['editFrom'],
            'editTo' => $arguments['editTo'],
        ];

        $result = $this->ticketsService->quickAddMilestone($params);

        if ($result) {
            return ToolResult::text("Milestone created successfully with ID: {$result}");
        }

        return ToolResult::error('Failed to create milestone.');
    }
}
