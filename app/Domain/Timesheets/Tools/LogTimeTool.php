<?php

namespace Leantime\Domain\Timesheets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Log;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 * Log time for a specific ticket.
 */
#[IsDestructive(false)]
class LogTimeTool extends Tool
{
    public function __construct(
        private Timesheets $timesheetsService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'ticketId' => $schema->integer()
                ->description('ID of the ticket to log time for.')
                ->required(),
            'hours' => $schema->number()
                ->description('Number of hours to log.')
                ->required(),
            'date' => $schema->string()
                ->description('Date for the time entry in ISO8601 format.')
                ->required(),
            'kind' => $schema->string()
                ->description('Type of work (e.g., GENERAL_BILLABLE, DEVELOPMENT).')
                ->required(),
            'description' => $schema->string()
                ->description('Description of the work performed.'),
        ];
    }

    public function name(): string
    {
        return 'logTime';
    }

    public function title(): string
    {
        return 'Log Time';
    }

    public function description(): string
    {
        return 'Logs time for a specific ticket.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

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

                return Response::text("Time entry successfully logged: {$hours} hours on ticket #{$ticketId}.");
            }

            return Response::error('Failed to log time entry. Please check the provided information.');
        } catch (\Exception $e) {
            Log::error('Error logging time: '.$e->getMessage());

            return Response::error('Failed to log time entry. Please try again.');
        }
    }
}
