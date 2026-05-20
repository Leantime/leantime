<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\McpException;
use Leantime\Core\Mcp\Policy\McpAccess;

class TicketsGet extends AbstractTool
{
    public function __construct(private McpAccess $access) {}

    public function name(): string
    {
        return 'tickets.get';
    }

    public function title(): string
    {
        return 'Get Ticket';
    }

    public function description(): string
    {
        return 'Returns one ticket when the principal can access its project.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['ticketId'],
            'properties' => [
                'ticketId' => ['type' => 'integer'],
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
        if (! isset($arguments['ticketId'])) {
            throw new McpException('ticketId is required', -32602, 400);
        }

        $ticket = $this->access->assertTicketAccess($context->principal, (int) $arguments['ticketId']);

        return [
            'ticket' => [
                'id' => (int) $ticket->id,
                'headline' => $ticket->headline,
                'description' => $ticket->description,
                'projectId' => (int) $ticket->projectId,
                'status' => $ticket->status,
                'type' => $ticket->type,
                'priority' => $ticket->priority,
                'editorId' => $ticket->editorId,
                'milestoneid' => $ticket->milestoneid,
                'modified' => $ticket->modified,
            ],
        ];
    }
}
