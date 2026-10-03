<?php

namespace Leantime\Domain\Projects\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Projects\Services\Projects;

/**
 * Gets all users assigned to a specific project.
 */
#[IsReadOnly]
class GetUsersAssignedToProjectTool extends Tool
{
    public function __construct(
        private Projects $projectService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('projectId')->description('ID of the project to get users for.')
            ->required()
            ->boolean('teamOnly')->description('Whether to only include direct team members.');
    }

    public function name(): string
    {
        return 'getUsersAssignedToProject';
    }

    public function description(): string
    {
        return 'Gets all users assigned to a specific project.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $projectId = (int) ($arguments['projectId'] ?? 0);
        $teamOnly = $arguments['teamOnly'] ?? false;

        // Only for a project the caller can view: getProject() returns false otherwise, so a
        // foreign project id must not expose its member list (names, emails, roles).
        if ($projectId <= 0 || ! $this->projectService->getProject($projectId)) {
            return ToolResult::error("Project {$projectId} was not found or you do not have access to it.");
        }

        $users = $this->projectService->getUsersAssignedToProject($projectId, $teamOnly);

        if (empty($users)) {
            return ToolResult::text('No users assigned to this project.');
        }

        $response = "## Users Assigned to Project\n";
        foreach ($users as $user) {
            $result = [
                'id' => $user['id'],
                'name' => Str::sanitizeForLLM($user['firstname'].' '.$user['lastname']),
                'email' => $user['username'],
                'role' => $user['role'] ?? 'Not specified',
                'projectRole' => $user['projectRole'] ?? 'Not specified',
            ];
            $response .= Str::toMarkdown($result)."\n";
        }

        return ToolResult::text($response);
    }
}
