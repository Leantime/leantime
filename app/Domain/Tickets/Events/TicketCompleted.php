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
     * @param  string  $type  The ticket type ('task', 'bug', 'milestone', ...; 'subtask' for a task with a parent).
     * @param  bool  $completedByAssignee  True when the acting user is the ticket's assignee.
     * @param  int|null  $daysToComplete  Whole days from the ticket's creation to now (null when unknown).
     * @param  bool  $hadDueDate  Whether the ticket had a due date.
     * @param  bool  $wasOverdue  Whether the due date had already passed.
     * @param  bool  $wasScheduledToday  Whether the ticket's planned start (editFrom) is today in the user's timezone.
     */
    public function __construct(
        public readonly int $ticketId,
        public readonly ?int $projectId,
        public readonly string $type = 'task',
        public readonly bool $completedByAssignee = false,
        public readonly ?int $daysToComplete = null,
        public readonly bool $hadDueDate = false,
        public readonly bool $wasOverdue = false,
        public readonly bool $wasScheduledToday = false,
    ) {}
}
