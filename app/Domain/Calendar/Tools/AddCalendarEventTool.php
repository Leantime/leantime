<?php

namespace Leantime\Domain\Calendar\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Calendar\Services\Calendar;

/**
 * Add a new calendar event.
 */
class AddCalendarEventTool extends Tool
{
    public function __construct(
        private Calendar $calendarService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('eventTitle')->description('Title of the event.')
            ->required()
            ->string('dateFrom')->description('Start date in user timezone format ISO8601 (example: 2024-04-30T15:00:00-04:00).')
            ->required()
            ->string('dateTo')->description('End date in user timezone format ISO8601 (example: 2024-04-30T15:00:00-04:00).')
            ->required()
            ->boolean('allDay')->description('Whether this is an all-day event or not.');
    }

    public function name(): string
    {
        return 'addEvent';
    }

    public function description(): string
    {
        return 'Adds a new calendar event.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $result = $this->calendarService->addEvent([
            'description' => $arguments['eventTitle'],
            'dateFrom' => $arguments['dateFrom'],
            'dateTo' => $arguments['dateTo'],
            'allDay' => $request->get('allDay', false),
        ]);

        if ($result) {
            return ToolResult::text("Event added successfully with ID: {$result}");
        }

        return ToolResult::error('Failed to add event.');
    }
}
