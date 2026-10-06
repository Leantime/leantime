<?php

namespace Leantime\Domain\Timesheets\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after the session user's running timer on a ticket was stopped and its time booked.
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class TimerStopped implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $ticketId  The ticket the timer ran on.
     * @param  float  $hours  The hours booked (0 for runs under a minute).
     * @param  bool  $automatic  True when the timer was stopped automatically because the ticket was completed.
     */
    public function __construct(
        public readonly int $ticketId,
        public readonly float $hours,
        public readonly bool $automatic,
    ) {}
}
