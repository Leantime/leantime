<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\Policy\McpAccess;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Projects\Services\Projects as ProjectService;

class ProjectsList extends AbstractTool
{
    public function __construct(
        private ProjectService $projectsService,
        private ProjectRepository $projectsRepository,
        private McpAccess $access,
    ) {}

    public function name(): string
    {
        return 'projects.list';
    }

    public function title(): string
    {
        return 'List Projects';
    }

    public function description(): string
    {
        return 'Lists projects visible to the authenticated principal.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'status' => ['type' => 'string', 'enum' => ['open', 'all'], 'default' => 'open'],
                'projectTypes' => ['type' => 'string', 'default' => 'all'],
            ],
        ];
    }

    public function requiredAbilities(): array
    {
        return ['mcp:read', 'projects:read'];
    }

    public function riskLevel(): string
    {
        return 'read';
    }

    public function execute(McpRequestContext $context, array $arguments): array
    {
        $status = $arguments['status'] ?? 'open';
        $projectTypes = $arguments['projectTypes'] ?? 'all';

        $projects = $this->access->canManageAllProjects($context->principal)
            ? $this->projectsRepository->getAll($status === 'all')
            : $this->projectsService->getProjectsAssignedToUser($context->principal->userId, $status, null, $projectTypes);

        return [
            'projects' => array_map(static fn (array $project) => [
                'id' => (int) $project['id'],
                'name' => $project['name'],
                'clientId' => isset($project['clientId']) ? (int) $project['clientId'] : null,
                'clientName' => $project['clientName'] ?? null,
                'state' => $project['state'] ?? null,
                'type' => $project['type'] ?? null,
                'modified' => $project['modified'] ?? null,
            ], $projects),
        ];
    }
}
