<?php

namespace Leantime\Domain\Projects\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Domain\Projects\Permissions\ProjectsPermissions;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

/**
 * Removes one user from one project's team (and leaves their other projects alone).
 */
#[IsIdempotent]
class RemoveUserFromProjectTool extends Tool
{
    public function __construct(
        private Projects $projectService,
        private PermissionService $permissionService,
        private ?UserRepository $users = null,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'userId' => $schema->integer()
                ->description('ID of the user.')
                ->required(),
            'projectId' => $schema->integer()
                ->description('ID of the project.')
                ->required(),
        ];
    }

    public function name(): string
    {
        return 'removeUserFromProject';
    }

    public function title(): string
    {
        return 'Remove User From Project';
    }

    public function description(): string
    {
        return "Removes a user from a project's team. Their other projects are not changed.";
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $userId = (int) ($arguments['userId'] ?? 0);
        $projectId = (int) ($arguments['projectId'] ?? 0);

        // MCP tools call services directly, past the RPC permission gate: check the same
        // permission the service method declares (global projects.edit, manager and up).
        if (! $this->permissionService->currentUserCan(ProjectsPermissions::EDIT, forceGlobal: true)) {
            return Response::error('You do not have permission to change project teams.');
        }

        if ($userId <= 0 || $projectId <= 0 || ! $this->projectService->getProject($projectId)) {
            return Response::error("Project {$projectId} was not found or you do not have access to it.");
        }

        // The service answers false for a user that doesn't exist too, which would read as
        // "already on the team" / "not on the team". Say what's actually wrong instead.
        $user = ($this->users ?? app(UserRepository::class))->getUser($userId);
        if (empty($user)) {
            return Response::error("User {$userId} was not found. Use getUsersAssignedToProject or another user lookup to find the right ID.");
        }

        if ($this->projectService->removeUserFromProject($userId, $projectId)) {
            return Response::text("User {$userId} was removed from project {$projectId}.");
        }

        return Response::text("User {$userId} is not on the team of project {$projectId}.");
    }
}
