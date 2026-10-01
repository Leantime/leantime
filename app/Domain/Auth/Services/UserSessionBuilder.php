<?php

namespace Leantime\Domain\Auth\Services;

use Leantime\Core\Support\NameSanitizer;
use Leantime\Domain\Auth\Models\Roles;

/**
 * Single source of truth for the `session('userdata')` array.
 *
 * Every authentication path — web login ({@see Auth::setUserSession}), x-api-key
 * ({@see \Leantime\Domain\Api\Services\Api::setApiUserSession}) and Sanctum/Bearer tokens
 * ({@see AuthUser::setUserSession}) — builds this same structure. They used to each build it
 * inline, which let fields drift silently between paths:
 *  - `role` was stored as the raw DB int on the Bearer path but as the role-NAME string on the
 *    others; the permission engine validates against the name list, so Bearer auth denied every
 *    gated method with -32001 (the 3.9.x regression).
 *  - `twoFAVerified` likewise diverged between the two token paths.
 *
 * Routing all three through this factory makes those bugs structurally impossible: `role` is
 * ALWAYS converted via {@see Roles::getRoleString()}, and every field is produced identically.
 * The two genuinely per-path values — whether the session is external-auth and whether 2FA is
 * already satisfied — are explicit parameters.
 */
class UserSessionBuilder
{
    /**
     * Build the canonical userdata array from a `zp_user` row.
     *
     * @param  array  $user  A zp_user row (id, firstname, username, profileId, clientId, role, …).
     * @param  bool  $isExternalAuth  True when the user authenticated via an external provider.
     * @param  bool  $twoFAVerified  True when 2FA is considered satisfied (token auth — the token
     *                               is the strong credential and no interactive 2FA is possible).
     * @return array<string, mixed> The userdata array to store in `session('userdata')`.
     */
    public static function build(array $user, bool $isExternalAuth = false, bool $twoFAVerified = false): array
    {
        return [
            'id' => (int) $user['id'],
            'name' => NameSanitizer::clean($user['firstname'] ?? ''),
            'profileId' => $user['profileId'] ?? '',
            'mail' => filter_var($user['username'] ?? '', FILTER_SANITIZE_EMAIL),
            'clientId' => $user['clientId'] ?? '',
            // ALWAYS the role-NAME string the permission engine validates against — never the raw
            // DB int. This is the field whose drift caused the Bearer -32001 regression.
            'role' => Roles::getRoleString($user['role']),
            'settings' => ! empty($user['settings']) ? safe_unserialize($user['settings'], []) : [],
            'twoFAEnabled' => $user['twoFAEnabled'] ?? false,
            'twoFAVerified' => $twoFAVerified,
            'twoFASecret' => $user['twoFASecret'] ?? '',
            'isExternalAuth' => $isExternalAuth,
            'createdOn' => ! empty($user['createdOn']) ? dtHelper()->parseDbDateTime($user['createdOn']) : dtHelper()->userNow(),
            'modified' => ! empty($user['modified']) ? dtHelper()->parseDbDateTime($user['modified']) : dtHelper()->userNow(),
        ];
    }
}
