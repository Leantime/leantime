<?php

namespace Leantime\Domain\Notifications\Repositories;

use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\DB;

/**
 * DeviceTokens — persistence for mobile push notification device tokens.
 *
 * Used by the Laravel Notifications delivery pipeline (issue #3398).
 * Supports two providers per row (the `provider` column):
 *   - 'expo': dispatch via Expo's push service (ExpoChannel)
 *   - 'fcm':  dispatch direct to Firebase Cloud Messaging
 *
 * Mobile registers a token on each login; backend stores per
 * (userId, token) and uses it + the provider when dispatching push.
 *
 * Lifecycle:
 *   - Register (upsert): mobile calls on login. lastSeenAt updated each time.
 *   - Unregister: mobile calls on logout — soft delete via invalidatedAt
 *     so we still have an audit trail.
 *   - Invalidate: backend marks tokens invalid when the provider reports
 *     the token is dead (Expo's DeviceNotRegistered, FCM's UNREGISTERED).
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
     * already exists, refresh lastSeenAt + provider and clear any prior
     * invalidation (mobile is telling us the token is alive again).
     *
     * @param  string  $provider  'expo' or 'fcm' — drives the send path
     *                            the dispatcher uses for this row.
     */
    public function save(int $userId, string $token, string $provider = 'expo', ?string $platform = null, ?string $deviceName = null): bool
    {
        $now = date('Y-m-d H:i:s');

        $this->db->table('zp_device_tokens')->updateOrInsert(
            ['userId' => $userId, 'token' => $token],
            [
                'provider' => $provider,
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
     * Return all valid (not-invalidated) push tokens for a user. Rows
     * include the `provider` column so the dispatch layer can route to
     * Expo or FCM as appropriate. Used by Notifications dispatch.
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
