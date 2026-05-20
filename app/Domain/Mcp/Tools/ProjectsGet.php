<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\McpException;
use Leantime\Core\Mcp\Policy\McpAccess;

class ProjectsGet extends AbstractTool
{
    public function __construct(private McpAccess $access) {}

    public function name(): string
    {
        return 'projects.get';
    }

    public function title(): string
    {
        return 'Get Project';
    }

    public function description(): string
    {
        return 'Returns a single project when the principal can access it.';
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
        return ['mcp:read', 'projects:read'];
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

        return [
            'project' => $this->access->assertProjectAccess($context->principal, (int) $arguments['projectId']),
        ];
    }
}
