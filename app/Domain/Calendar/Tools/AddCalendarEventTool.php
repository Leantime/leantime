<?php

namespace Leantime\Domain\Calendar\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Leantime\Domain\Calendar\Services\Calendar;

/**
 * Add a new calendar event.
 */
#[IsDestructive(false)]
class AddCalendarEventTool extends Tool
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
                ->description('Whether this is an all-day event or not.'),
        ];
    }

    public function name(): string
    {
        return 'addEvent';
    }

    public function title(): string
    {
        return 'Add Event';
    }

    public function description(): string
    {
        return 'Adds a new calendar event.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $result = $this->calendarService->addEvent([
            'description' => $arguments['eventTitle'],
            'dateFrom' => $arguments['dateFrom'],
            'dateTo' => $arguments['dateTo'],
            'allDay' => $arguments['allDay'] ?? false,
        ]);

        if ($result) {
            return Response::text("Event added successfully with ID: {$result}");
        }

        return Response::error('Failed to add event.');
    }
}
