<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\McpException;
use Leantime\Core\Mcp\Policy\McpAccess;
use Leantime\Domain\Mcp\Services\TicketWriter;

class TicketsUpdate extends AbstractTool
{
    public function __construct(
        private TicketWriter $ticketWriter,
        private McpAccess $access,
    ) {}

    public function name(): string
    {
        return 'tickets.update';
    }

    public function title(): string
    {
        return 'Update Ticket';
    }

    public function description(): string
    {
        return 'Updates ticket fields beyond status using optimistic concurrency and explicit actor checks.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['ticketId', 'expectedVersion'],
            'properties' => [
                'ticketId' => ['type' => 'integer'],
                'expectedVersion' => ['type' => 'string'],
                'headline' => ['type' => 'string'],
                'type' => ['type' => 'string'],
                'description' => ['type' => 'string'],
                'status' => ['type' => 'integer'],
                'date' => ['type' => 'string'],
                'dateToFinish' => ['type' => 'string'],
                'priority' => ['type' => 'integer'],
                'hourRemaining' => ['type' => 'number'],
                'planHours' => ['type' => 'number'],
                'tags' => ['type' => 'string'],
                'editorId' => ['type' => 'integer'],
                'acceptanceCriteria' => ['type' => 'string'],
                'dependingTicketId' => ['type' => 'integer'],
                'milestoneid' => ['type' => 'integer'],
                'collaborators' => ['type' => 'array'],
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
        $expectedVersion = (string) ($arguments['expectedVersion'] ?? '');
        if ($ticketId <= 0 || $expectedVersion === '') {
            throw new McpException('ticketId and expectedVersion are required', -32602, 400);
        }

        $this->access->assertTicketAccess($context->principal, $ticketId);
        if (! in_array($context->principal->role, ['editor', 'manager', 'admin', 'owner'], true)) {
            throw new McpException('Only editors or above can update tickets', -32003, 403);
        }

        return $this->ticketWriter->updateTicket($context->principal, $ticketId, $expectedVersion, $arguments);
    }
}
