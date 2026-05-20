<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\McpException;
use Leantime\Core\Mcp\Policy\McpAccess;
use Leantime\Domain\Mcp\Services\ProjectWriter;
use Leantime\Domain\Projects\Repositories\Projects;

class ProjectsUpdate extends AbstractTool
{
    public function __construct(
        private ProjectWriter $projectWriter,
        private Projects $projectsRepository,
        private McpAccess $access,
    ) {}

    public function name(): string
    {
        return 'projects.update';
    }

    public function title(): string
    {
        return 'Update Project';
    }

    public function description(): string
    {
        return 'Updates a project with explicit MCP access and optimistic concurrency.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['projectId', 'expectedVersion'],
            'properties' => [
                'projectId' => ['type' => 'integer'],
                'expectedVersion' => ['type' => 'string'],
                'name' => ['type' => 'string'],
                'details' => ['type' => 'string'],
                'clientId' => ['type' => 'integer'],
                'state' => ['type' => 'integer'],
                'hourBudget' => ['type' => 'number'],
                'dollarBudget' => ['type' => 'number'],
                'psettings' => ['type' => 'string', 'enum' => ['restricted', 'clients', 'all']],
                'menuType' => ['type' => 'string'],
                'type' => ['type' => 'string'],
                'parent' => ['type' => 'integer'],
                'start' => ['type' => 'string'],
                'end' => ['type' => 'string'],
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
        if ($projectId <= 0 || $expectedVersion === '') {
            throw new McpException('projectId and expectedVersion are required', -32602, 400);
        }

        $this->access->assertProjectAccess($context->principal, $projectId);
        if (! in_array($context->principal->role, ['manager', 'admin', 'owner'], true)) {
            throw new McpException('Only managers or above can update projects', -32003, 403);
        }

        return $this->projectWriter->updateProject($context->principal, $projectId, $expectedVersion, $arguments);
    }
}
