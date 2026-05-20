<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\McpException;
use Leantime\Core\Mcp\Policy\McpAccess;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;

class TicketsUpdateStatus extends AbstractTool
{
    public function __construct(
        private TicketRepository $ticketRepository,
        private McpAccess $access,
    ) {}

    public function name(): string
    {
        return 'tickets.update_status';
    }

    public function title(): string
    {
        return 'Update Ticket Status';
    }

    public function description(): string
    {
        return 'Changes a ticket status with optimistic concurrency based on the ticket modified timestamp.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['ticketId', 'status', 'expectedVersion'],
            'properties' => [
                'ticketId' => ['type' => 'integer'],
                'status' => ['type' => 'integer'],
                'expectedVersion' => ['type' => 'string'],
                'sortIndex' => ['type' => 'integer'],
                'projectId' => ['type' => 'integer'],
            ],
        ];
    }

    public function requiredAbilities(): array
    {
        return ['mcp:write', 'tickets:write'];
    }

    public function riskLevel(): string
    {
        return 'write';
    }

    public function supportsAsync(): bool
    {
        return true;
    }

    public function requiresIdempotency(): bool
    {
        return true;
    }

    public function scopeProjectId(McpRequestContext $context, array $arguments): int
    {
        $ticketId = (int) ($arguments['ticketId'] ?? 0);
        if ($ticketId <= 0) {
            return 0;
        }

        $ticket = $this->access->assertTicketAccess($context->principal, $ticketId);

        return (int) $ticket->projectId;
    }

    public function execute(McpRequestContext $context, array $arguments): array
    {
        $ticketId = (int) ($arguments['ticketId'] ?? 0);
        $status = (int) ($arguments['status'] ?? -999);
        $expectedVersion = (string) ($arguments['expectedVersion'] ?? '');

        if ($ticketId <= 0 || $expectedVersion === '' || ! array_key_exists('status', $arguments)) {
            throw new McpException('ticketId, status, and expectedVersion are required', -32602, 400);
        }

        if (! array_key_exists($status, $this->ticketRepository->getStatusList())) {
            throw new McpException('status is not valid for tickets', -32602, 400);
        }

        $ticket = $this->access->assertTicketAccess($context->principal, $ticketId);
        $updated = $this->ticketRepository->updateTicketStatusForActor(
            actorId: $context->principal->userId,
            ticketId: $ticketId,
            status: $status,
            ticketSorting: (int) ($arguments['sortIndex'] ?? -1),
            handler: 'mcp',
            expectedModified: $expectedVersion,
        );

        if (! $updated) {
            throw new McpException('Ticket version conflict', -32009, 409, [
                'ticketId' => $ticketId,
                'currentVersion' => $ticket->modified,
            ]);
        }

        $freshTicket = $this->ticketRepository->getTicket($ticketId);

        return [
            'ticketId' => $ticketId,
            'projectId' => (int) $ticket->projectId,
            'status' => $status,
            'modified' => $freshTicket !== false ? $freshTicket->modified : null,
        ];
    }
}
