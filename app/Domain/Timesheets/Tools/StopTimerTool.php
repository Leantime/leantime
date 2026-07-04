<?php

namespace Leantime\Domain\Timesheets\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 * Stop a running timer.
 */
#[Name('stopTimer')]
#[Description('Stop a running timer and complete any associated timesheet entries.')]
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
            'ticketId' => $schema->string()
                ->description('Timer to stop on a specific ticket.')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $ticketId = $request->string('ticketId');

        if ($ticketId) {
            $this->timesheetsService->punchOut($ticketId);
        }

        return Response::text('Timer stopped successfully.');
    }
}
