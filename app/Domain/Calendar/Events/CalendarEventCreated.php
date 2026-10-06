<?php

namespace Leantime\Domain\Calendar\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a calendar event was created.
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class CalendarEventCreated implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $eventId  The new event id.
     * @param  bool  $allDay  Whether it is an all-day event.
     */
    public function __construct(
        public readonly int $eventId,
        public readonly bool $allDay,
    ) {}
}
