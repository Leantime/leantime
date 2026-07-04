<?php

namespace Leantime\Domain\Timesheets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Log;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 * Log time for a specific ticket.
 */
#[Name('logTime')]
#[Description('Logs time for a specific ticket. This allows users to record time spent on tasks.')]
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

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        try {
            $ticketId = $request->integer('ticketId');
            $params = [
                'date' => $request->string('date'),
                'hours' => $request->get('hours'),
                'kind' => $request->string('kind'),
                'description' => $request->string('description', ''),
            ];

            $result = $this->timesheetsService->logTime($ticketId, $params);

            if ($result) {
                $hours = $request->get('hours');

                return Response::text("Time entry successfully logged: {$hours} hours on ticket #{$ticketId}.");
            }

            return Response::error('Failed to log time entry. Please check the provided information.');
        } catch (\Exception $e) {
            Log::error('Error logging time: '.$e->getMessage());

            return Response::error('Failed to log time entry. Please try again.');
        }
    }
}
