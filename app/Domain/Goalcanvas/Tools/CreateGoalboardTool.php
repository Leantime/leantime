<?php

namespace Leantime\Domain\Goalcanvas\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;

/**
 * Create a new goal board.
 */
class CreateGoalboardTool extends Tool
{
    public function __construct(
        private Goalcanvas $goalcanvasService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('title')->description('Title of the goal board.')
            ->required()
            ->integer('projectId')->description('Project ID this goal board belongs to.')
            ->required()
            ->string('description')->description('Description of the goal board.');
    }

    public function name(): string
    {
        return 'createGoalboard';
    }

    public function description(): string
    {
        return 'Creates a new goal board for organizing goals within a project.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $values = [
            'title' => $arguments['title'],
            'description' => ($arguments['description'] ?? ''),
            'projectId' => (int) ($arguments['projectId'] ?? 0),
            'author' => session('userdata.id'),
        ];

        $boardId = $this->goalcanvasService->createGoalboard($values);

        if ($boardId) {
            return ToolResult::text("Goal board created successfully with ID: {$boardId}");
        }

        return ToolResult::error('Failed to create goal board. Please check the provided information.');
    }
}
