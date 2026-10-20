<?php

namespace Leantime\Domain\Comments\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Leantime\Domain\Comments\Services\Comments;
use Leantime\Domain\Projects\Services\Projects;

/**
 * Add a project status update with a red/yellow/green indicator.
 */
#[IsDestructive(false)]
class AddProjectStatusUpdateTool extends Tool
{
    public function __construct(
        private Comments $commentsService,
        private Projects $projectService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'projectId' => $schema->integer()
                ->description('Project ID to add status update to.')
                ->required(),
            'text' => $schema->string()
                ->description('Status update text.')
                ->required(),
            'status' => $schema->string()
                ->description('Status indicator (green, yellow, red).')
                ->required(),
        ];
    }

    public function name(): string
    {
        return 'addProjectStatusUpdate';
    }

    public function title(): string
    {
        return 'Add Project Status Update';
    }

    public function description(): string
    {
        return 'Adds a new status update to a project with a red/yellow/green indicator.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $projectId = (int) ($arguments['projectId'] ?? 0);
        $status = $arguments['status'];

        if (! in_array($status, ['green', 'yellow', 'red'])) {
            return Response::error("Invalid status value. Must be 'green', 'yellow', or 'red'.");
        }

        $project = $this->projectService->getProject($projectId);
        if (! $project) {
            return Response::error("Project not found: ID {$projectId}");
        }

        $values = [
            'text' => $arguments['text'],
            'father' => 0,
            'status' => $status,
        ];

        $result = $this->commentsService->addComment($values, 'project', $projectId, $project);

        if ($result) {
            return Response::text("Project status update added successfully with status: {$status}");
        }

        return Response::error('Failed to add status update. Please check the provided information.');
    }
}
