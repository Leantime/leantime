<?php

namespace Leantime\Domain\Tickets\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a ticket's planned work window (editFrom/editTo) was set or moved to a new non-empty value (calendar drags, scheduling tools, the ticket form).
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class TicketScheduled implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $ticketId  The scheduled ticket.
     * @param  int|null  $projectId  The ticket's project, when known.
     * @param  string|null  $editFrom  The new start, UTC 'Y-m-d H:i:s' (null when unset).
     * @param  string|null  $editTo  The new end, UTC 'Y-m-d H:i:s' (null when unset).
     * @param  bool  $rescheduled  True when the ticket already had a start or end before this write.
     */
    public function __construct(
        public readonly int $ticketId,
        public readonly ?int $projectId,
        public readonly ?string $editFrom,
        public readonly ?string $editTo,
        public readonly bool $rescheduled,
    ) {}
}
