<?php

namespace Leantime\Domain\Oidc\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Short-lived, single-use codes for the mobile SSO bridge, kept in the cache.
 *
 * The OIDC callback (mobile-origin branch) mints one AFTER the user is
 * authenticated and hands it to the app via the app-scheme redirect. The app
 * POSTs it to /oidc/mobile/exchange to receive a bearer token. Passing a code
 * (not the token) through the redirect URL is what keeps a scheme-hijacking app
 * from lifting a usable token — it would have to win the HTTPS exchange race for
 * a <=60s, single-use code.
 *
 * Cache, not the DB: these live for at most 60s and are consumed once, so a TTL
 * cache entry is the natural fit (and self-expiring — no sweep needed). The code
 * is hashed into the cache key so the raw code is never stored. Cache::pull
 * reads-and-deletes, so a replayed code can't be exchanged twice.
 *
 * PKCE: each code is bound to the app-supplied `code_challenge`. The exchange
 * requires the matching verifier, so a code intercepted from the app-scheme
 * redirect is useless without the secret the app kept (see Controllers/Mobile).
 *
 * NOTE (deployment): this uses the default cache store. On a multi-node install
 * that store must be SHARED across app nodes (e.g. redis) — the code is written
 * by the callback request and read by a later exchange request that may land on
 * a different node. The per-node file store ('installation') is fine for a
 * single-node install but would miss on multi-node.
 */
class OidcMobileCode
{
    private const KEY_PREFIX = 'oidc.mobile.code.';

    private const TTL_SECONDS = 60;

    /**
     * Mint a code bound to a user id + the PKCE code_challenge. Returns the RAW
     * code (only its hash is used as the cache key, so the raw value is never
     * persisted).
     */
    public function createCode(int $userId, ?string $codeChallenge = null): string
    {
        $code = Str::random(64);

        Cache::put(
            $this->key($code),
            ['userId' => $userId, 'challenge' => $codeChallenge],
            self::TTL_SECONDS
        );

        return $code;
    }

    /**
     * Validate + consume a code. Returns ['userId' => int, 'challenge' => ?string]
     * or null if the code is unknown/expired. Single-use: Cache::pull
     * reads-and-deletes, so a replayed code fails.
     */
    public function consumeCode(string $rawCode): ?array
    {
        $data = Cache::pull($this->key($rawCode));

        if (! is_array($data) || ! isset($data['userId'])) {
            return null;
        }

        return [
            'userId' => (int) $data['userId'],
            'challenge' => $data['challenge'] ?? null,
        ];
    }

    private function key(string $rawCode): string
    {
        return self::KEY_PREFIX.hash('sha256', $rawCode);
    }
}
