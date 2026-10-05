<?php

namespace Leantime\Domain\Users\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after an invitation email was sent to an invited user (first invite or resend).
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class InviteSent implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $userId  The id of the invited user (the invitee).
     */
    public function __construct(
        public readonly int $userId,
    ) {}
}
