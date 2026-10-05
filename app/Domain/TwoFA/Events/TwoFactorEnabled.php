<?php

namespace Leantime\Domain\TwoFA\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a user verified a code and enabled two-factor authentication.
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class TwoFactorEnabled implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $userId  The user who enabled 2FA.
     */
    public function __construct(
        public readonly int $userId,
    ) {}
}
