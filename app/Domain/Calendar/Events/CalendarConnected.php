<?php

namespace Leantime\Domain\Calendar\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a user connected a calendar: generated their iCal feed link or added an external calendar.
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class CalendarConnected implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  string  $kind  'ical_feed' or 'external_calendar'.
     */
    public function __construct(
        public readonly string $kind,
    ) {}
}
