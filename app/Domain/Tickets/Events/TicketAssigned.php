<?php

namespace Leantime\Domain\Tickets\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a ticket got a (new) assignee — on creation with an assignee and whenever editorId changes to a different non-empty user.
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class TicketAssigned implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $ticketId  The assigned ticket.
     * @param  int|null  $projectId  The ticket's project, when known.
     * @param  int  $assigneeId  The new assignee's user id.
     * @param  int|null  $previousAssigneeId  The assignee before this write (null when unassigned or on creation).
     * @param  bool  $assignedToSelf  True when the acting user assigned the ticket to themselves.
     */
    public function __construct(
        public readonly int $ticketId,
        public readonly ?int $projectId,
        public readonly int $assigneeId,
        public readonly ?int $previousAssigneeId,
        public readonly bool $assignedToSelf,
    ) {}
}
