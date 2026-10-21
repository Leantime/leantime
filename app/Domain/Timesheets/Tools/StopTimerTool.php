<?php

namespace Leantime\Domain\Timesheets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 * Stop a running timer.
 */
class StopTimerTool extends Tool
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
                ->description('Ticket ID to stop the timer for. Omit to stop the active timer.'),
        ];
    }

    public function name(): string
    {
        return 'stopTimer';
    }

    public function title(): string
    {
        return 'Stop Timer';
    }

    public function description(): string
    {
        return 'Stop a running timer.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $ticketId = ($arguments['ticketId'] ?? null);

        if ($ticketId !== null && (int) $ticketId > 0) {
            $this->timesheetsService->punchOut((int) $ticketId);
        } else {
            $this->timesheetsService->stopActiveTimer();
        }

        return Response::text('Timer stopped successfully.');
    }
}
