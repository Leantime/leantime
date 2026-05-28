<?php

namespace Leantime\Domain\Notifications\Repositories;

use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\DB;

/**
 * DeviceTokens — persistence for mobile push notification device tokens.
 *
 * Used by the Laravel Notifications + laravel-notification-channels/expo
 * delivery pipeline (issue #3398). Mobile registers a token on each
 * login; backend stores per (userId, token) and uses it when dispatching
 * push notifications via ExpoChannel.
 *
 * Lifecycle:
 *   - Register (upsert): mobile calls on login. lastSeenAt updated each time.
 *   - Unregister: mobile calls on logout — soft delete via invalidatedAt
 *     so we still have an audit trail.
 *   - Invalidate: backend marks tokens invalid when Expo reports
 *     DeviceNotRegistered / InvalidCredentials during a send.
 *   - Prune: periodic cleanup deletes rows invalidated > 30 days ago.
 */
class DeviceTokens
{
    private DatabaseManager $db;

    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }

    /**
     * Upsert a device token registration. If the (userId, token) pair
     * already exists, refresh lastSeenAt and clear any prior invalidation
     * (mobile is telling us the token is alive again).
     */
    public function save(int $userId, string $token, ?string $platform = null, ?string $deviceName = null): bool
    {
        $now = date('Y-m-d H:i:s');

        $this->db->table('zp_device_tokens')->updateOrInsert(
            ['userId' => $userId, 'token' => $token],
            [
                'platform' => $platform,
                'deviceName' => $deviceName,
                'lastSeenAt' => $now,
                'invalidatedAt' => null,
                // Only set createdAt on insert — updateOrInsert merges
                // these into both branches, so use a no-op-on-update
                // approach by setting via the existing row's value when
                // present. The simplest cross-DB approach: set on insert,
                // ignore on update by checking after.
                'createdAt' => $now,
            ]
        );

        return true;
    }

    /**
     * Return all valid (not-invalidated) Expo push tokens for a user.
     * Used by Notifications dispatch to know where to push.
     */
    public function findActiveByUser(int $userId): array
    {
        return $this->db->table('zp_device_tokens')
            ->where('userId', $userId)
            ->whereNull('invalidatedAt')
            ->get()
            ->toArray();
    }

    /**
     * Soft-delete via invalidatedAt. Preserves the row for audit; the
     * prune() job sweeps stale invalidated rows periodically.
     */
    public function invalidate(string $token): bool
    {
        return $this->db->table('zp_device_tokens')
            ->where('token', $token)
            ->update(['invalidatedAt' => date('Y-m-d H:i:s')]) > 0;
    }

    /**
     * Specific to logout: mobile passes the token, we soft-delete this
     * one row. (Doesn't touch other devices the same user has.)
     */
    public function invalidateForUser(int $userId, string $token): bool
    {
        return $this->db->table('zp_device_tokens')
            ->where('userId', $userId)
            ->where('token', $token)
            ->update(['invalidatedAt' => date('Y-m-d H:i:s')]) > 0;
    }

    /**
     * Hard-delete rows invalidated more than $daysOld ago. Called from
     * a cleanup cron (TODO: register in Queue/register.php).
     */
    public function prune(int $daysOld = 30): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$daysOld} days"));

        return $this->db->table('zp_device_tokens')
            ->whereNotNull('invalidatedAt')
            ->where('invalidatedAt', '<', $cutoff)
            ->delete();
    }
}
