<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\McpException;
use Leantime\Core\Mcp\Policy\McpAccess;
use Leantime\Domain\Mcp\Services\TicketWriter;

class TicketsCreate extends AbstractTool
{
    public function __construct(
        private TicketWriter $ticketWriter,
        private McpAccess $access,
    ) {}

    public function name(): string
    {
        return 'tickets.create';
    }

    public function title(): string
    {
        return 'Create Ticket';
    }

    public function description(): string
    {
        return 'Creates a ticket with explicit MCP actor and project checks.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['projectId', 'headline'],
            'properties' => [
                'projectId' => ['type' => 'integer'],
                'headline' => ['type' => 'string'],
                'type' => ['type' => 'string'],
                'description' => ['type' => 'string'],
                'editorId' => ['type' => 'integer'],
                'dateToFinish' => ['type' => 'string'],
                'timeToFinish' => ['type' => 'string'],
                'status' => ['type' => 'integer'],
                'planHours' => ['type' => 'number'],
                'tags' => ['type' => 'string'],
                'sprint' => ['type' => 'integer'],
                'storypoints' => ['type' => 'number'],
                'hourRemaining' => ['type' => 'number'],
                'priority' => ['type' => 'integer'],
                'acceptanceCriteria' => ['type' => 'string'],
                'editFrom' => ['type' => 'string'],
                'timeFrom' => ['type' => 'string'],
                'editTo' => ['type' => 'string'],
                'timeTo' => ['type' => 'string'],
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
        return (int) ($arguments['projectId'] ?? 0);
    }

    public function execute(McpRequestContext $context, array $arguments): array
    {
        $projectId = (int) ($arguments['projectId'] ?? 0);
        $headline = trim((string) ($arguments['headline'] ?? ''));
        if ($projectId <= 0 || $headline === '') {
            throw new McpException('projectId and headline are required', -32602, 400);
        }

        $this->access->assertProjectAccess($context->principal, $projectId);
        if (! in_array($context->principal->role, ['editor', 'manager', 'admin', 'owner'], true)) {
            throw new McpException('Only editors or above can create tickets', -32003, 403);
        }

        return $this->ticketWriter->createTicket($context->principal, $arguments);
    }
}
