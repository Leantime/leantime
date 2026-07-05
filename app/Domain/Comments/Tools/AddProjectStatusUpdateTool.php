<?php

namespace Leantime\Domain\Comments\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Comments\Services\Comments;
use Leantime\Domain\Projects\Services\Projects;

/**
 * Add a project status update with a red/yellow/green indicator.
 */
class AddProjectStatusUpdateTool extends Tool
{
    public function __construct(
        private Comments $commentsService,
        private Projects $projectService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('projectId')->description('Project ID to add status update to.')
            ->required()
            ->string('text')->description('Status update text.')
            ->required()
            ->string('status')
            ->description('Status indicator (green, yellow, red).')
            ->required();
    }

    public function name(): string
    {
        return 'addProjectStatusUpdate';
    }

    public function description(): string
    {
        return 'Adds a new status update to a project with a red/yellow/green indicator.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $projectId = (int) ($arguments['projectId'] ?? 0);
        $status = $arguments['status'];

        if (! in_array($status, ['green', 'yellow', 'red'])) {
            return ToolResult::error("Invalid status value. Must be 'green', 'yellow', or 'red'.");
        }

        $project = $this->projectService->getProject($projectId);
        if (! $project) {
            return ToolResult::error("Project not found: ID {$projectId}");
        }

        $values = [
            'text' => $arguments['text'],
            'father' => 0,
            'status' => $status,
        ];

        $result = $this->commentsService->addComment($values, 'project', $projectId, $project);

        if ($result) {
            return ToolResult::text("Project status update added successfully with status: {$status}");
        }

        return ToolResult::error('Failed to add status update. Please check the provided information.');
    }
}
