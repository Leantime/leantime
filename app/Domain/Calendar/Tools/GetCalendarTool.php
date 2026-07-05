<?php

namespace Leantime\Domain\Calendar\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Calendar\Services\Calendar;
use Leantime\Domain\Calendar\Support\CalendarEventFormatter;

/**
 * Get all calendar events for the current user.
 */
#[IsReadOnly]
class GetCalendarTool extends Tool
{
    public function __construct(
        private Calendar $calendarService,
    ) {}

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('from')->description('Starting date of the date range to look for. In user timezone format ISO8601 (example: 2024-04-30T15:00:00-04:00).')
            ->required()
            ->string('until')->description('End date of the date range to look for. In user timezone format ISO8601 (example: 2024-04-30T15:00:00-04:00).')
            ->required();
    }

    public function name(): string
    {
        return 'getCalendar';
    }

    public function description(): string
    {
        return 'Gets all calendar events for the current user including tasks with due dates and scheduled work times.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $from = $arguments['from'];
        $until = $arguments['until'];

        $events = $this->calendarService->getCalendar(session('userdata.id'), $from, $until);

        $maxEvents = 100;
        $numEvents = 0;

        $response = "## Calendar Events (tasks, and events)\n";
        foreach ($events as $event) {
            if ($numEvents < $maxEvents) {
                $enhancedEvent = $event;
                $enhancedEvent['dateFrom'] = dtHelper()->parseDbDateTime($event['dateFrom'])->setToUserTimezone()->toIso8601String();
                $enhancedEvent['dateTo'] = dtHelper()->parseDbDateTime($event['dateTo'])->setToUserTimezone()->toIso8601String();

                $formatter = new CalendarEventFormatter($enhancedEvent);
                $response .= $formatter->format()."\n\n";
                $numEvents++;
            }
        }

        $externalEvents = $this->calendarService->getExternalCalendarEvents($from, $until);
        $response .= "\n## External Calendar Events (ICAL imports)\n";
        foreach ($externalEvents as $event) {
            if ($numEvents < $maxEvents) {
                $enhancedEvent = $event;
                $enhancedEvent['dateFrom'] = dtHelper()->parseDbDateTime($event['dateFrom'])->setToUserTimezone()->toIso8601String();
                $enhancedEvent['dateTo'] = dtHelper()->parseDbDateTime($event['dateTo'])->setToUserTimezone()->toIso8601String();

                $formatter = new CalendarEventFormatter($enhancedEvent);
                $response .= $formatter->format()."\n\n";
                $numEvents++;
            }
        }

        return ToolResult::text($response ?: 'No calendar events found.');
    }
}
