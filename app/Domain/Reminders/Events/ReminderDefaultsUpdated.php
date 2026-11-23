<?php

namespace Leantime\Domain\Reminders\Events;

use Leantime\Core\Events\Concerns\InteractsWithClientEvents;
use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\EmitsClientEvent;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after someone changed their reminder defaults (their default for to-dos, or their morning
 * summary). On htmx requests it also tells the browser (lt:reminders:defaults.updated), so every
 * loaded "Remind me" menu redraws: what a to-do inherits may have changed.
 */
final class ReminderDefaultsUpdated implements EmitsClientEvent, LeantimeEvent
{
    use InteractsWithClientEvents, InteractsWithEvents;

    public const CLIENT_EVENT = 'lt:reminders:defaults.updated';

    public function __construct(
        public readonly int $userId,
    ) {}
}
