<?php

namespace Leantime\Domain\Projects\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
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

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'projectId' => $schema->integer()
                ->description('ID of the project to get users for.')
                ->required(),
            'teamOnly' => $schema->boolean()
                ->description('Whether to only include direct team members.'),
        ];
    }

    public function name(): string
    {
        return 'getUsersAssignedToProject';
    }

    public function title(): string
    {
        return 'Get Users Assigned To Project';
    }

    public function description(): string
    {
        return 'Gets all users assigned to a specific project.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $projectId = (int) ($arguments['projectId'] ?? 0);
        $teamOnly = $arguments['teamOnly'] ?? false;

        // Only for a project the caller can view: getProject() returns false otherwise, so a
        // foreign project id must not expose its member list (names, emails, roles).
        if ($projectId <= 0 || ! $this->projectService->getProject($projectId)) {
            return Response::error("Project {$projectId} was not found or you do not have access to it.");
        }

        $users = $this->projectService->getUsersAssignedToProject($projectId, $teamOnly);

        if (empty($users)) {
            return Response::text('No users assigned to this project.');
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

        return Response::text($response);
    }
}
