<?php

namespace Leantime\Domain\Oidc\Services;

use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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
 * is hashed into the cache key so the raw code is never stored. consumeCode()
 * guards its read-and-delete with a cache lock, so a replayed or concurrently
 * exchanged code can't be exchanged twice.
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

    private const LOCK_SECONDS = 5;

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
     * Non-destructive read of a code's payload. Callers verify PKCE against
     * the returned challenge FIRST, then call consumeCode() to burn the code.
     * This prevents a scheme-hijacker from DoSing legit logins by submitting
     * an intercepted code with a bad verifier (which would delete the code
     * before the real client's exchange arrived).
     */
    public function peekCode(string $rawCode): ?array
    {
        $data = Cache::get($this->key($rawCode));

        if (! is_array($data) || ! isset($data['userId'])) {
            return null;
        }

        return [
            'userId' => (int) $data['userId'],
            'challenge' => $data['challenge'] ?? null,
        ];
    }

    /**
     * Burn the code once and report whether THIS caller consumed it.
     *
     * The read-and-delete is guarded by a non-blocking cache lock keyed on the
     * code: of two concurrent exchanges that both called peekCode() on it, only
     * the lock holder performs the get()+forget() and can return true — so at
     * most one exchange mints from a single-use code. A caller that can't take
     * the lock (a concurrent consume is in flight) gets false and must not mint;
     * so does an already-consumed or unknown code.
     *
     * (A bare Cache::pull() is NOT used precisely because it is get()+forget(),
     * not a single atomic op on any driver — two callers could both read the code
     * before either deletes it, and both mint. The lock closes that window.)
     */
    public function consumeCode(string $rawCode): bool
    {
        $key = $this->key($rawCode);

        // Fail CLOSED: without atomic locks we cannot guarantee single-use, so
        // refuse rather than fall back to a racy non-atomic consume (which would
        // reintroduce the double-mint window this method exists to prevent). All
        // default Leantime stores implement LockProvider; a store here that
        // doesn't is a misconfiguration worth surfacing loudly, not degrading to.
        if (! Cache::getStore() instanceof LockProvider) {
            Log::error('OidcMobileCode: cache store does not support atomic locks; refusing mobile SSO code consume. Configure a lock-capable cache store (file/redis/memcached/database).');

            return false;
        }

        $lock = Cache::lock($key.'.lock', self::LOCK_SECONDS);

        // Non-blocking: the loser of a concurrent consume gets false immediately
        // instead of waiting, and its exchange is rejected.
        if (! $lock->get()) {
            return false;
        }

        try {
            $existed = Cache::get($key) !== null;
            Cache::forget($key);

            return $existed;
        } finally {
            $lock->release();
        }
    }

    private function key(string $rawCode): string
    {
        return self::KEY_PREFIX.hash('sha256', $rawCode);
    }
}
