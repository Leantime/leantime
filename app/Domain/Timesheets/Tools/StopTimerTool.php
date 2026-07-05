<?php

namespace Leantime\Domain\Timesheets\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 * Stop a running timer.
 */
class StopTimerTool extends Tool
{
    public function __construct(
        private Timesheets $timesheetsService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('ticketId')->description('Ticket ID to stop the timer for. Omit to stop the active timer.');
    }

    public function name(): string
    {
        return 'stopTimer';
    }

    public function description(): string
    {
        return 'Stop a running timer.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $ticketId = ($arguments['ticketId'] ?? null);

        if ($ticketId !== null && (int) $ticketId > 0) {
            $this->timesheetsService->punchOut((int) $ticketId);
        } else {
            $this->timesheetsService->stopActiveTimer();
        }

        return ToolResult::text('Timer stopped successfully.');
    }
}
