<?php

namespace Leantime\Domain\Timesheets\Tools;

use Illuminate\Support\Facades\Log;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 * Log time for a specific ticket.
 */
class LogTimeTool extends Tool
{
    public function __construct(
        private Timesheets $timesheetsService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->integer('ticketId')->description('ID of the ticket to log time for.')
            ->required()
            ->number('hours')->description('Number of hours to log.')
            ->required()
            ->string('date')->description('Date for the time entry in ISO8601 format.')
            ->required()
            ->string('kind')->description('Type of work (e.g., GENERAL_BILLABLE, DEVELOPMENT).')
            ->required()
            ->string('description')->description('Description of the work performed.');
    }

    public function name(): string
    {
        return 'logTime';
    }

    public function description(): string
    {
        return 'Logs time for a specific ticket.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        try {
            $ticketId = (int) ($arguments['ticketId'] ?? 0);
            $params = [
                'date' => $arguments['date'],
                'hours' => ($arguments['hours'] ?? null),
                'kind' => $arguments['kind'],
                'description' => ($arguments['description'] ?? ''),
            ];

            $result = $this->timesheetsService->logTime($ticketId, $params);

            if ($result) {
                $hours = ($arguments['hours'] ?? null);

                return ToolResult::text("Time entry successfully logged: {$hours} hours on ticket #{$ticketId}.");
            }

            return ToolResult::error('Failed to log time entry. Please check the provided information.');
        } catch (\Exception $e) {
            Log::error('Error logging time: '.$e->getMessage());

            return ToolResult::error('Failed to log time entry. Please try again.');
        }
    }
}
