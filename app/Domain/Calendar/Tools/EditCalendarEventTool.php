<?php

namespace Leantime\Domain\Calendar\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Leantime\Domain\Calendar\Services\Calendar;

/**
 * Edit an existing calendar event.
 */
#[IsIdempotent]
class EditCalendarEventTool extends Tool
{
    public function __construct(
        private Calendar $calendarService,
    ) {}

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->description('Event ID to edit.')
                ->required(),
            'eventTitle' => $schema->string()
                ->description('Title of the event.')
                ->required(),
            'dateFrom' => $schema->string()
                ->description('Start date in user timezone format ISO8601 (example: 2024-04-30T15:00:00-04:00).')
                ->required(),
            'dateTo' => $schema->string()
                ->description('End date in user timezone format ISO8601 (example: 2024-04-30T15:00:00-04:00).')
                ->required(),
            'allDay' => $schema->boolean()
                ->description('Whether this is an all-day event.'),
        ];
    }

    public function name(): string
    {
        return 'editEvent';
    }

    public function title(): string
    {
        return 'Edit Event';
    }

    public function description(): string
    {
        return 'Edits an existing calendar event.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $result = $this->calendarService->editEvent([
            'id' => (int) ($arguments['id'] ?? 0),
            'description' => $arguments['eventTitle'],
            'dateFrom' => $arguments['dateFrom'],
            'dateTo' => $arguments['dateTo'],
            'allDay' => $arguments['allDay'] ?? false,
        ]);

        if ($result) {
            return Response::text('Event updated successfully.');
        }

        return Response::error('Failed to update event.');
    }
}
