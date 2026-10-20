<?php

namespace Leantime\Domain\Calendar\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
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

    public function name(): string
    {
        return 'getCalendar';
    }

    public function title(): string
    {
        return 'Get Calendar';
    }

    public function description(): string
    {
        return 'Gets all calendar events for the current user including tasks with due dates and scheduled work times.';
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

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

        if ($numEvents === 0) {
            return Response::text('No calendar events found for the specified date range.');
        }

        return Response::text($response);
    }
}
