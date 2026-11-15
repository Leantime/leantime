<?php

declare(strict_types=1);

namespace Leantime\Core\Auth;

/**
 * Whether a user account may authenticate.
 *
 * `zp_user.status` is 'a' for active accounts (some rows store 'A'), 'i' for invited users who
 * have not accepted yet, and '' / '0' for accounts an admin deactivated. Login only accepts
 * active accounts; every credential that outlives the login (web session, API key, Bearer token)
 * checks the same rule so deactivating a user takes effect on their next request.
 */
final class AccountStatus
{
    public const ACTIVE = 'a';

    /**
     * Whether the given zp_user row belongs to an active account. A missing row is not active.
     *
     * @param  array<string, mixed>|false|null  $user  The zp_user row.
     */
    public static function isActive(array|false|null $user): bool
    {
        if (! is_array($user)) {
            return false;
        }

        $status = strtolower(trim((string) ($user['status'] ?? '')));

        return $status === self::ACTIVE;
    }
}
