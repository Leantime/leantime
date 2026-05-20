<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\McpException;
use Leantime\Domain\Mcp\Services\ProjectWriter;
use Leantime\Domain\Projects\Repositories\Projects;

class ProjectsCreate extends AbstractTool
{
    public function __construct(
        private ProjectWriter $projectWriter,
        private Projects $projectsRepository,
    ) {}

    public function name(): string
    {
        return 'projects.create';
    }

    public function title(): string
    {
        return 'Create Project';
    }

    public function description(): string
    {
        return 'Creates a project with explicit MCP actor context and safe defaults.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['name', 'clientId'],
            'properties' => [
                'name' => ['type' => 'string'],
                'clientId' => ['type' => 'integer'],
                'details' => ['type' => 'string'],
                'hourBudget' => ['type' => 'number'],
                'dollarBudget' => ['type' => 'number'],
                'psettings' => ['type' => 'string', 'enum' => ['restricted', 'clients', 'all']],
                'menuType' => ['type' => 'string'],
                'type' => ['type' => 'string'],
                'parent' => ['type' => 'integer'],
                'start' => ['type' => 'string'],
                'end' => ['type' => 'string'],
                'assignedUsers' => ['type' => 'array'],
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

    public function execute(McpRequestContext $context, array $arguments): array
    {
        if (! in_array($context->principal->role, ['manager', 'admin', 'owner'], true)) {
            throw new McpException('Only managers or above can create projects', -32003, 403);
        }

        $name = trim((string) ($arguments['name'] ?? ''));
        $clientId = (int) ($arguments['clientId'] ?? 0);
        $psettings = (string) ($arguments['psettings'] ?? 'restricted');

        if ($name === '' || $clientId <= 0) {
            throw new McpException('name and clientId are required', -32602, 400);
        }

        if (! in_array($psettings, ['restricted', 'clients', 'all'], true)) {
            throw new McpException('psettings must be restricted, clients, or all', -32602, 400);
        }

        $projectId = $this->projectWriter->createProject($context->principal->userId, $arguments + ['psettings' => $psettings]);
        if ($projectId === false) {
            throw new McpException('Project could not be created', -32000, 500);
        }

        $project = $this->projectsRepository->getProject($projectId);

        return [
            'projectId' => $projectId,
            'project' => $project,
            'status' => 'created',
        ];
    }
}
