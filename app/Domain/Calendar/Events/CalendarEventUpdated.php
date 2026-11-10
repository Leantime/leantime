<?php

namespace Leantime\Domain\Calendar\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a calendar event was edited or moved (drag, resize).
 */
final class CalendarEventUpdated implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $eventId  The updated event id.
     */
    public function __construct(
        public readonly int $eventId,
    ) {}
}
