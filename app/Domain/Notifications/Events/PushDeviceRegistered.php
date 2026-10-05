<?php

namespace Leantime\Domain\Notifications\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a mobile device registered its push token.
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class PushDeviceRegistered implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $userId  The device owner.
     * @param  string  $platform  'ios' or 'android'.
     */
    public function __construct(
        public readonly int $userId,
        public readonly string $platform,
    ) {}
}
