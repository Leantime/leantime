<?php

namespace Leantime\Domain\Tickets\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired when a ticket's status changes INTO a status whose statusType is DONE from a status that was not DONE. Fires at most once per ticket per service instance (request).
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class TicketCompleted implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $ticketId  The completed ticket id.
     * @param  int|null  $projectId  The ticket's project id, when known.
     */
    public function __construct(
        public readonly int $ticketId,
        public readonly ?int $projectId,
    ) {}
}
