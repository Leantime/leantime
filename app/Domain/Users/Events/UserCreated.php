<?php

namespace Leantime\Domain\Users\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a new user account row was created, from any creation path.
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class UserCreated implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $userId  The id of the created user.
     * @param  int  $role  The role key the user was created with (0 when none was given).
     * @param  string  $source  Which path created the user: 'admin', 'invite', 'api', 'ldap', 'oidc', 'install', or an external auth provider name.
     */
    public function __construct(
        public readonly int $userId,
        public readonly int $role,
        public readonly string $source,
    ) {}
}
