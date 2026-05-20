<?php

namespace Leantime\Domain\Mcp\Tools;

use Leantime\Core\Mcp\Context\McpRequestContext;
use Leantime\Core\Mcp\McpException;
use Leantime\Core\Mcp\Policy\McpAccess;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Timesheets\Services\Timesheets;

class TimesheetsLog extends AbstractTool
{
    public function __construct(
        private TicketRepository $ticketRepository,
        private Timesheets $timesheetsService,
        private McpAccess $access,
    ) {}

    public function name(): string
    {
        return 'timesheets.log';
    }

    public function title(): string
    {
        return 'Log Timesheet Entry';
    }

    public function description(): string
    {
        return 'Logs time against a ticket using explicit actor and project access checks.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['ticketId', 'kind', 'hours'],
            'oneOf' => [
                ['required' => ['date']],
                ['required' => ['dateString']],
                ['required' => ['timestamp']],
            ],
            'properties' => [
                'ticketId' => ['type' => 'integer'],
                'kind' => ['type' => 'string'],
                'hours' => ['type' => 'number'],
                'date' => ['type' => 'string'],
                'dateString' => ['type' => 'string'],
                'timestamp' => ['type' => 'integer'],
                'description' => ['type' => 'string'],
            ],
        ];
    }

    public function requiredAbilities(): array
    {
        return ['mcp:write', 'timesheets:write'];
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
        if ($ticketId <= 0) {
            throw new McpException('ticketId is required', -32602, 400);
        }

        if (! isset($arguments['date'], $arguments['dateString'], $arguments['timestamp'])
            && ! array_key_exists('date', $arguments)
            && ! array_key_exists('dateString', $arguments)
            && ! array_key_exists('timestamp', $arguments)) {
            throw new McpException('One of date, dateString, or timestamp is required', -32602, 400);
        }

        $ticket = $this->access->assertTicketAccess($context->principal, $ticketId);
        $this->timesheetsService->logTime($ticketId, $arguments + ['userId' => $context->principal->userId]);

        return [
            'ticketId' => $ticketId,
            'projectId' => (int) $ticket->projectId,
            'userId' => $context->principal->userId,
            'status' => 'logged',
        ];
    }
}
