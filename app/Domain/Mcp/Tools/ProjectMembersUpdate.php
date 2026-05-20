<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\McpException;
use Leantime\Core\Mcp\Policy\McpAccess;
use Leantime\Domain\Mcp\Services\ProjectWriter;

class ProjectMembersUpdate extends AbstractTool
{
    public function __construct(
        private ProjectWriter $projectWriter,
        private McpAccess $access,
    ) {}

    public function name(): string
    {
        return 'projects.members.update';
    }

    public function title(): string
    {
        return 'Update Project Members';
    }

    public function description(): string
    {
        return 'Replaces the direct project member list using explicit project roles and optimistic concurrency.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['projectId', 'expectedVersion', 'members'],
            'properties' => [
                'projectId' => ['type' => 'integer'],
                'expectedVersion' => ['type' => 'string'],
                'members' => ['type' => 'array'],
            ],
        ];
    }

    public function requiredAbilities(): array
    {
        return ['mcp:write', 'projects:write'];
    }

    public function riskLevel(): string
    {
        return 'admin';
    }

    public function requiresIdempotency(): bool
    {
        return true;
    }

    public function supportsAsync(): bool
    {
        return true;
    }

    public function scopeProjectId(McpRequestContext $context, array $arguments): int
    {
        return (int) ($arguments['projectId'] ?? 0);
    }

    public function execute(McpRequestContext $context, array $arguments): array
    {
        $projectId = (int) ($arguments['projectId'] ?? 0);
        $expectedVersion = (string) ($arguments['expectedVersion'] ?? '');
        $members = $arguments['members'] ?? null;

        if ($projectId <= 0 || $expectedVersion === '' || ! is_array($members)) {
            throw new McpException('projectId, expectedVersion, and members are required', -32602, 400);
        }

        $this->access->assertProjectAccess($context->principal, $projectId);
        if (! in_array($context->principal->role, ['manager', 'admin', 'owner'], true)) {
            throw new McpException('Only managers or above can update project members', -32003, 403);
        }

        return $this->projectWriter->updateProjectMembers($projectId, $expectedVersion, $members);
    }
}
