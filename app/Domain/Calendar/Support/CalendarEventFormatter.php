<?php

namespace Leantime\Domain\Calendar\Support;

use Illuminate\Support\Str;
use Leantime\Core\Support\AbstractEntityFormatter;

/**
 * Calendar event formatter for AI consumption.
 *
 * Formats calendar events into structured markdown suitable for AI prompts,
 * embeddings, and other LLM operations. Handles all event types: calendar,
 * ticket, and external events.
 */
class CalendarEventFormatter extends AbstractEntityFormatter
{
    /**
     * Fields to exclude from calendar event formatting.
     */
    protected array $excludedFields = [
        'backgroundColor',
        'borderColor',
        'url',
        'className',
    ];

    /**
     * Priority order for displaying calendar event fields.
     */
    protected array $fieldPriority = [
        'id',
        'title',
        'description',
        'eventType',
        'dateContext',
        'startDate',
        'endDate',
        'allDay',
        'projectId',
    ];

    public function __construct(
        protected array $event
    ) {}

    /**
     * Get the entity type.
     */
    public function getEntityType(): string
    {
        return 'calendar_event';
    }

    /**
     * Get the entity ID.
     */
    public function getEntityId(): mixed
    {
        return $this->event['id'] ?? null;
    }

    /**
     * Prepare calendar event data for formatting.
     */
    protected function prepareEntityData(array $context = []): array
    {
        $data = [
            'id' => $this->event['id'] ?? null,
            'title' => $this->sanitizeValue($this->event['title'] ?? ''),
            'description' => $this->sanitizeValue($this->event['description'] ?? ''),
            'eventType' => $this->sanitizeValue($this->event['eventType'] ?? ''),
            'dateContext' => $this->sanitizeValue($this->event['dateContext'] ?? ''),
            'startDate' => $this->formatDate($this->event['dateFrom'] ?? ''),
            'endDate' => $this->formatDate($this->event['dateTo'] ?? ''),
            'allDay' => $this->formatAllDay($this->event['allDay'] ?? false),
            'duration' => $this->calculateDuration(),
            'projectId' => $this->event['projectId'] ?? null,
            'projectName' => $this->sanitizeValue($this->event['projectName'] ?? ''),
            'eventTypeFormatted' => $this->formatEventType(),
            'dateContextFormatted' => $this->formatDateContext(),
        ];

        // Add ticket-specific information if this is a ticket event
        if (($this->event['eventType'] ?? '') === 'ticket') {
            $data['ticketDetails'] = $this->formatTicketDetails();
        }

        // Add external calendar information if this is an external event
        if (($this->event['eventType'] ?? '') === 'external') {
            $data['externalCalendarInfo'] = $this->formatExternalCalendarInfo();
        }

        return $data;
    }

    /**
     * Format the header section.
     */
    protected function formatHeader(array $data): string
    {
        $eventTypeEmoji = match ($data['eventType']) {
            'calendar' => '📅',
            'ticket' => '🎫',
            'external' => '🔗',
            default => '📝'
        };

        $allDayIndicator = $data['allDay'] === 'Yes' ? ' (All Day)' : '';
        $projectInfo = ! empty($data['projectName']) ? " - {$data['projectName']}" : '';

        return "## {$eventTypeEmoji} {$data['title']}{$allDayIndicator}{$projectInfo}";
    }

    /**
     * Format the body with custom calendar event formatting.
     */
    protected function formatBody(array $data, array $context = []): string
    {
        $filteredData = $this->filterFields($data, $context);

        // Remove the duplicated header fields from body
        unset($filteredData['id'], $filteredData['title'], $filteredData['projectName']);

        $sortedData = $this->sortFields($filteredData);

        return "\n".Str::toMarkdown($sortedData);
    }

    /**
     * Format a compact summary.
     */
    protected function formatSummary(array $data): string
    {
        $eventTypeEmoji = match ($data['eventType']) {
            'calendar' => '📅',
            'ticket' => '🎫',
            'external' => '🔗',
            default => '📝'
        };

        $timeInfo = $this->formatTimeRange($data['startDate'], $data['endDate'], $data['allDay'] === 'Yes');

        return "{$eventTypeEmoji} {$data['title']} ({$timeInfo})";
    }

    /**
     * Format the all-day flag.
     *
     * @param  mixed  $allDay
     */
    protected function formatAllDay($allDay): string
    {
        if (is_bool($allDay)) {
            return $allDay ? 'Yes' : 'No';
        }

        if (is_string($allDay)) {
            return strtolower($allDay) === 'true' ? 'Yes' : 'No';
        }

        return 'No';
    }

    /**
     * Calculate and format event duration.
     */
    protected function calculateDuration(): string
    {
        $dateFrom = $this->event['dateFrom'] ?? '';
        $dateTo = $this->event['dateTo'] ?? '';

        if (empty($dateFrom) || empty($dateTo)) {
            return 'Unknown';
        }

        try {
            $start = \DateTime::createFromFormat('Y-m-d H:i:s', $dateFrom);
            $end = \DateTime::createFromFormat('Y-m-d H:i:s', $dateTo);

            if (! $start || ! $end) {
                return 'Unknown';
            }

            $diff = $start->diff($end);

            if ($diff->days > 0) {
                return $diff->days.' day(s)';
            } elseif ($diff->h > 0) {
                $minutes = $diff->i > 0 ? " {$diff->i}min" : '';

                return $diff->h.'h'.$minutes;
            } elseif ($diff->i > 0) {
                return $diff->i.' minutes';
            } else {
                return 'Less than 1 minute';
            }
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Format the event type with description.
     */
    protected function formatEventType(): string
    {
        return match ($this->event['eventType'] ?? '') {
            'calendar' => '📅 Calendar Event',
            'ticket' => '🎫 Task/Ticket Event',
            'external' => '🔗 External Calendar Import',
            default => '📝 Other Event'
        };
    }

    /**
     * Format the date context with description.
     */
    protected function formatDateContext(): string
    {
        return match ($this->event['dateContext'] ?? '') {
            'plan' => '📋 Planned Event',
            'due' => '⏰ Due Date',
            'edit' => '⚙️ Scheduled Work Time',
            default => '📅 General Event'
        };
    }

    /**
     * Format ticket-specific details.
     */
    protected function formatTicketDetails(): array
    {
        $details = [];

        if (isset($this->event['ticketId'])) {
            $details['ticketId'] = $this->event['ticketId'];
        }

        if (isset($this->event['status'])) {
            $details['status'] = $this->sanitizeValue($this->event['status']);
        }

        if (isset($this->event['priority'])) {
            $details['priority'] = $this->formatPriority($this->event['priority']);
        }

        if (isset($this->event['assignedTo'])) {
            $details['assignedTo'] = $this->sanitizeValue($this->event['assignedTo']);
        }

        return $details;
    }

    /**
     * Format external calendar information.
     */
    protected function formatExternalCalendarInfo(): array
    {
        $details = [];

        if (isset($this->event['calendarId'])) {
            $details['calendarId'] = $this->sanitizeValue($this->event['calendarId']);
        }

        if (isset($this->event['externalId'])) {
            $details['externalId'] = $this->sanitizeValue($this->event['externalId']);
        }

        if (isset($this->event['lastSync'])) {
            $details['lastSync'] = $this->formatDate($this->event['lastSync']);
        }

        return $details;
    }

    /**
     * Format time range for summaries.
     */
    protected function formatTimeRange(string $startDate, string $endDate, bool $allDay): string
    {
        if ($allDay) {
            try {
                $start = \DateTime::createFromFormat(\DateTime::ATOM, $startDate);
                if ($start) {
                    return $start->format('M j, Y');
                }
            } catch (\Exception $e) {
                // Fall through to default
            }

            return 'All Day';
        }

        try {
            $start = \DateTime::createFromFormat(\DateTime::ATOM, $startDate);
            $end = \DateTime::createFromFormat(\DateTime::ATOM, $endDate);

            if ($start && $end) {
                if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
                    // Same day
                    return $start->format('M j, Y g:i A').' - '.$end->format('g:i A');
                } else {
                    // Multi-day
                    return $start->format('M j, Y g:i A').' - '.$end->format('M j, Y g:i A');
                }
            }
        } catch (\Exception $e) {
            // Fall through to default
        }

        return 'Time not available';
    }
}
