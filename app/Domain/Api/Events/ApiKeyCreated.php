<?php

namespace Leantime\Domain\Api\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a new API key (an API service-account user) was created.
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class ApiKeyCreated implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $apiUserId  The id of the API key's user row.
     */
    public function __construct(
        public readonly int $apiUserId,
    ) {}
}
