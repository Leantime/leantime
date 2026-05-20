<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\McpException;
use Leantime\Core\Mcp\Policy\McpAccess;
use Leantime\Domain\Mcp\Services\TicketSearch;

class TicketsSearch extends AbstractTool
{
    public function __construct(
        private TicketSearch $ticketSearch,
        private McpAccess $access,
    ) {}

    public function name(): string
    {
        return 'tickets.search';
    }

    public function title(): string
    {
        return 'Search Tickets';
    }

    public function description(): string
    {
        return 'Searches tickets within a single project using explicit MCP access control.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['projectId'],
            'properties' => [
                'projectId' => ['type' => 'integer'],
                'term' => ['type' => 'string'],
                'status' => ['type' => 'array', 'items' => ['type' => 'integer']],
                'editorId' => ['type' => 'integer'],
                'type' => ['type' => 'string'],
                'limit' => ['type' => 'integer'],
            ],
        ];
    }

    public function requiredAbilities(): array
    {
        return ['mcp:read', 'tickets:read'];
    }

    public function riskLevel(): string
    {
        return 'read';
    }

    public function execute(McpRequestContext $context, array $arguments): array
    {
        $projectId = (int) ($arguments['projectId'] ?? 0);
        if ($projectId <= 0) {
            throw new McpException('projectId is required', -32602, 400);
        }

        $this->access->assertProjectAccess($context->principal, $projectId);

        return [
            'tickets' => $this->ticketSearch->searchProjectTickets($projectId, $arguments),
        ];
    }
}
