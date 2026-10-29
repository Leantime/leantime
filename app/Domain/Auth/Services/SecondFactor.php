<?php

namespace Leantime\Domain\Auth\Services;

use RobThree\Auth\TwoFactorAuth;

/**
 * Verifies a user's time-based one-time code against their stored 2FA secret.
 *
 * Session-free on purpose: the interactive web flow (Auth::verify2FA) reads the
 * secret from the session, but the mobile SSO exchange has no session and only
 * the zp_user row, so both go through here. Also the seam tests stub instead of
 * generating real TOTP codes.
 */
class SecondFactor
{
    private const ISSUER = 'Leantime';

    /**
     * @param  string  $secret  The user's base32 TOTP secret (zp_user.twoFASecret).
     * @param  string  $code  The code the user typed.
     * @param  int|null  $time  Unix time to verify against; null means now. For tests.
     */
    public function verify(string $secret, string $code, ?int $time = null): bool
    {
        if ($secret === '' || $code === '') {
            return false;
        }

        return (new TwoFactorAuth(self::ISSUER))->verifyCode($secret, $code, 1, $time);
    }
}
