<?php

namespace Leantime\Domain\Auth\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after an invited user finished account setup (onboarding) and was activated — the self-signup / invite-acceptance success point.
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class UserRegistered implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $userId  The id of the user who registered.
     */
    public function __construct(
        public readonly int $userId,
    ) {}
}
