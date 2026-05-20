<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\McpException;
use Leantime\Core\Mcp\Policy\McpAccess;
use Leantime\Domain\Projects\Services\Projects as ProjectService;

class ProjectMembers extends AbstractTool
{
    public function __construct(
        private ProjectService $projectsService,
        private McpAccess $access,
    ) {}

    public function name(): string
    {
        return 'projects.members';
    }

    public function title(): string
    {
        return 'List Project Members';
    }

    public function description(): string
    {
        return 'Lists project members for a project the principal can access.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['projectId'],
            'properties' => [
                'projectId' => ['type' => 'integer'],
            ],
        ];
    }

    public function requiredAbilities(): array
    {
        return ['mcp:read', 'projects:read', 'users:read'];
    }

    public function riskLevel(): string
    {
        return 'read';
    }

    public function execute(McpRequestContext $context, array $arguments): array
    {
        if (! isset($arguments['projectId'])) {
            throw new McpException('projectId is required', -32602, 400);
        }

        $projectId = (int) $arguments['projectId'];
        $this->access->assertProjectAccess($context->principal, $projectId);
        $members = $this->projectsService->getUsersAssignedToProject($projectId, true) ?: [];

        return [
            'members' => array_map(static fn (array $member) => [
                'id' => (int) $member['id'],
                'firstname' => $member['firstname'],
                'lastname' => $member['lastname'],
                'username' => $member['username'],
                'role' => $member['role'],
                'projectRole' => $member['projectRole'] ?? null,
                'status' => $member['status'] ?? null,
            ], $members),
        ];
    }
}
