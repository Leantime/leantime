<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Domain\Mcp\Repositories\Mcp as McpRepository;

class ApprovalsList extends AbstractTool
{
    public function __construct(private McpRepository $mcpRepository) {}

    public function name(): string
    {
        return 'approvals.list';
    }

    public function title(): string
    {
        return 'List MCP Approvals';
    }

    public function description(): string
    {
        return 'Lists pending and historical MCP approval requests visible to the principal.';
    }

    public function inputSchema(): array
    {
        return ['type' => 'object', 'properties' => []];
    }

    public function requiredAbilities(): array
    {
        return ['mcp:read'];
    }

    public function riskLevel(): string
    {
        return 'read';
    }

    public function execute(McpRequestContext $context, array $arguments): array
    {
        $includeAll = in_array($context->principal->role, ['manager', 'admin', 'owner'], true);

        return [
            'approvals' => $this->mcpRepository->listApprovalsForUser($context->principal->userId, $includeAll),
        ];
    }
}
