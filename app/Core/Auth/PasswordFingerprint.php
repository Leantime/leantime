<?php

namespace Leantime\Core\Auth;

/**
 * Ties a web session to the password the user logged in with.
 *
 * At login the session stores a fingerprint of the user's current password hash. On every
 * request {@see \Leantime\Core\Middleware\AuthenticateSession} compares it with the hash in
 * the database: once the password changes (reset, admin edit, own change elsewhere) every
 * other session carrying the old fingerprint is logged out. The session that made the change
 * refreshes its own fingerprint so it stays signed in.
 *
 * Only a sha256 digest of the hash is kept in the session, never the hash itself.
 */
final class PasswordFingerprint
{
    public const SESSION_KEY = 'userdata.pwfp';

    /**
     * Fingerprint of a stored password hash (an empty hash — external auth users — is valid input).
     */
    public static function of(?string $passwordHash): string
    {
        return hash('sha256', 'leantime-session-pw|'.($passwordHash ?? ''));
    }

    /**
     * Whether the fingerprint stored in a session still matches the current password hash.
     */
    public static function matches(string $storedFingerprint, ?string $currentPasswordHash): bool
    {
        return hash_equals($storedFingerprint, self::of($currentPasswordHash));
    }

    /**
     * Refresh the current session's fingerprint after the session user changed their own password,
     * so this session stays valid while all other sessions of the user are invalidated.
     *
     * No-op outside an authenticated session or when the password belongs to another user.
     */
    public static function refreshForSessionUser(int $userId, string $newPasswordHash): void
    {
        if (! app()->bound('session')) {
            return;
        }

        if ((int) session('userdata.id') !== $userId || $userId === 0) {
            return;
        }

        // Token-authenticated (API key / Bearer) sessions never carry a fingerprint.
        if (! session()->has(self::SESSION_KEY)) {
            return;
        }

        session([self::SESSION_KEY => self::of($newPasswordHash)]);
    }
}
