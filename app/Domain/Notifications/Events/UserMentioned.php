<?php

namespace Leantime\Domain\Notifications\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired once per mentioned user that was actually notified (in-app + email) about an @mention.
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class UserMentioned implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $mentionedUserId  The user who was mentioned.
     * @param  string  $module  The module the mention lives in (comments, tickets, projects, canvas).
     * @param  int|null  $moduleId  The id of the entity holding the mention.
     * @param  int|null  $projectId  The project of the notification, when known.
     */
    public function __construct(
        public readonly int $mentionedUserId,
        public readonly string $module,
        public readonly ?int $moduleId,
        public readonly ?int $projectId,
    ) {}
}
