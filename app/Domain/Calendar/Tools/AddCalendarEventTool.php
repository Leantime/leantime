<?php

namespace Leantime\Domain\Calendar\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Leantime\Domain\Calendar\Services\Calendar;

/**
 * Add a new calendar event.
 */
#[Name('addEvent')]
#[Description('Adds a new calendar event. Note: This can only add Leantime calendar events, not external calendar events. The order of the parameters is important with the event title first then dateFrom and then dateTo.')]
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
                ->description('Whether this is an all-day event or not.')
                ->default(false),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $result = $this->calendarService->addEvent([
            'description' => $request->string('eventTitle'),
            'dateFrom' => $request->string('dateFrom'),
            'dateTo' => $request->string('dateTo'),
            'allDay' => $request->get('allDay', false),
        ]);

        if ($result) {
            return Response::text("Event added successfully with ID: {$result}");
        }

        return Response::error('Failed to add event.');
    }
}
