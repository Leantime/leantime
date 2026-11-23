<?php

namespace Leantime\Domain\Reminders\Events;

use Leantime\Core\Events\Concerns\InteractsWithClientEvents;
use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\EmitsClientEvent;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after someone changed how often one to-do reminds them.
 *
 * On htmx requests it also tells the browser (lt:reminders:reminder.updated, plus the name scoped to
 * the to-do), so every other "Remind me" menu already loaded for that to-do redraws with the new
 * choice. The menu that made the change already shows it.
 */
final class TaskReminderUpdated implements EmitsClientEvent, LeantimeEvent
{
    use InteractsWithClientEvents, InteractsWithEvents;

    public const CLIENT_EVENT = 'lt:reminders:reminder.updated';

    public function __construct(
        public readonly int $ticketId,
        public readonly int $userId,
    ) {}

    /**
     * Scope the client event to the to-do.
     */
    public function clientScope(): int
    {
        return $this->ticketId;
    }
}
