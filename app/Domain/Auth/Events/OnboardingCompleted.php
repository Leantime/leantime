<?php

namespace Leantime\Domain\Auth\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a user completed the invite onboarding flow (the historical `onboarding_finished` point).
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class OnboardingCompleted implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $userId  The id of the user who finished onboarding.
     */
    public function __construct(
        public readonly int $userId,
    ) {}
}
