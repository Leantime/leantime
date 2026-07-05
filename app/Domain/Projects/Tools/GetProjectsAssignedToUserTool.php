<?php

namespace Leantime\Domain\Projects\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Projects\Services\Projects;

/**
 * Gets all projects assigned to a specific user.
 */
#[IsReadOnly]
class GetProjectsAssignedToUserTool extends Tool
{
    public function __construct(
        private Projects $projectService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('userId')->description('User ID to get projects for.')
            ->required()
            ->string('projectStatus')->description('Filter by project status (open, closed, all).')
            ->integer('clientId')->description('Filter by client ID.')
            ->string('projectTypes')->description('Filter by project types (comma-separated: project, program, etc.). Use "all" for all project types.');
    }

    public function name(): string
    {
        return 'getProjectsAssignedToUser';
    }

    public function description(): string
    {
        return 'Gets all projects assigned to a specific user.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $userId = (int) ($arguments['userId'] ?? 0);
        $projectStatus = ($arguments['projectStatus'] ?? 'open');
        $clientId = ($arguments['clientId'] ?? null);
        $projectTypes = ($arguments['projectTypes'] ?? 'all');

        if ($userId === 0) {
            $userId = session('userdata.id') ?? '';
        }

        $projects = $this->projectService->getProjectsAssignedToUser($userId, $projectStatus, $clientId, $projectTypes);

        $response = "## Projects Assigned to User\n";
        foreach ($projects as $project) {
            $result = [
                'id' => $project['id'],
                'name' => Str::sanitizeForLLM($project['name']),
                'clientName' => Str::sanitizeForLLM($project['clientName']),
                'type' => $project['type'],
                'state' => $project['state'],
                'start' => $project['start'] ?? 'Not set',
                'end' => $project['end'] ?? 'Not set',
            ];
            $response .= Str::toMarkdown($result)."\n";
        }

        if (empty($projects)) {
            return ToolResult::text('No projects found.');
        }

        return ToolResult::text($response);
    }
}
