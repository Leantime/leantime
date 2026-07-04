<?php

namespace Leantime\Domain\Calendar\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Leantime\Domain\Calendar\Services\Calendar;
use Leantime\Domain\Calendar\Support\CalendarEventFormatter;

/**
 * Get all calendar events for the current user.
 */
#[Name('getCalendar')]
#[Description('Gets all calendar events for the current user including tasks with due dates and scheduled work times and events from external calendars. Dates come back in UTC.')]
#[IsReadOnly]
class GetCalendarTool extends Tool
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
            'from' => $schema->string()
                ->description('Starting date of the date range to look for. In user timezone format ISO8601 (example: 2024-04-30T15:00:00-04:00).')
                ->required(),
            'until' => $schema->string()
                ->description('End date of the date range to look for. In user timezone format ISO8601 (example: 2024-04-30T15:00:00-04:00).')
                ->required(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $from = $request->string('from');
        $until = $request->string('until');

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

        return Response::text($response ?: 'No calendar events found.');
    }
}
