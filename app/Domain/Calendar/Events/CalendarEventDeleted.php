<?php

namespace Leantime\Domain\Calendar\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a calendar event was deleted.
 */
final class CalendarEventDeleted implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $eventId  The deleted event id.
     */
    public function __construct(
        public readonly int $eventId,
    ) {}
}
