<?php

namespace Leantime\Domain\Timesheets\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after the session user started a timer (punch in) on a ticket.
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class TimerStarted implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $ticketId  The ticket the timer runs on.
     */
    public function __construct(
        public readonly int $ticketId,
    ) {}
}
