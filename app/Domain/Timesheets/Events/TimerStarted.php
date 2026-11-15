<?php

namespace Leantime\Domain\Timesheets\Events;

use Leantime\Core\Events\Concerns\InteractsWithClientEvents;
use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\EmitsClientEvent;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after the session user started a timer (punch in) on a ticket.
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 * On htmx requests it tells the browser lt:timesheets:timer.started, so timer buttons and the header stopwatch refresh.
 */
final class TimerStarted implements EmitsClientEvent, LeantimeEvent
{
    use InteractsWithClientEvents, InteractsWithEvents;

    public const CLIENT_EVENT = 'lt:timesheets:timer.started';

    /**
     * @param  int  $ticketId  The ticket the timer runs on.
     */
    public function __construct(
        public readonly int $ticketId,
    ) {}
}
