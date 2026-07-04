<?php

namespace Leantime\Domain\Projects\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Projects\Services\Projects;

/**
 * Gets all users assigned to a specific project.
 */
#[Name('getUsersAssignedToProject')]
#[Description('Gets all users assigned to a specific project.')]
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

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $projectId = $request->integer('projectId');
        $teamOnly = $request->get('teamOnly', false);

        $users = $this->projectService->getUsersAssignedToProject($projectId, $teamOnly ?? false);

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
