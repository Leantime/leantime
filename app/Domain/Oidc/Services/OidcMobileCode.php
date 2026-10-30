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
 * Cache, not the DB: these live for at most 60s (180s once a second factor is
 * pending, see below) and are consumed once, so a TTL cache entry is the natural
 * fit (and self-expiring — no sweep needed). The code is hashed into the cache
 * key so the raw code is never stored. consumeCode() guards its read-and-delete
 * with a cache lock, so a replayed or concurrently exchanged code can't be
 * exchanged twice.
 *
 * PKCE: each code is bound to the app-supplied `code_challenge`. The exchange
 * requires the matching verifier, so a code intercepted from the app-scheme
 * redirect is useless without the secret the app kept (see Controllers/Mobile).
 *
 * Second factor: when the code's user has Leantime 2FA, the exchange answers the
 * first PKCE-verified request with two_factor_required and the app asks for the
 * code. extendForSecondFactor() gives that one code 180s to come back (once, and
 * only after PKCE proved the caller is the app); recordSecondFactorFailure()
 * counts wrong codes so the exchange can burn the code after a few.
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

    /** Lifetime once the exchange is waiting for the user's second factor. */
    public const SECOND_FACTOR_TTL_SECONDS = 180;

    /** Wrong second-factor codes a one-time code survives; the limit burns it. */
    public const MAX_SECOND_FACTOR_ATTEMPTS = 5;

    private const LOCK_SECONDS = 5;

    /**
     * Mint a code bound to a user id + the PKCE code_challenge. Returns the RAW
     * code (only its hash is used as the cache key, so the raw value is never
     * persisted).
     */
    public function createCode(int $userId, ?string $codeChallenge = null): string
    {
        $code = Str::random(64);

        $this->store($this->key($code), [
            'userId' => $userId,
            'challenge' => $codeChallenge,
            'expiresAt' => time() + self::TTL_SECONDS,
            'secondFactorExtended' => false,
            'secondFactorFailures' => 0,
        ]);

        return $code;
    }

    /**
     * Non-destructive read of a code's payload. Callers verify PKCE against
     * the returned challenge FIRST, then call consumeCode() to burn the code.
     * This prevents a scheme-hijacker from DoSing legit logins by submitting
     * an intercepted code with a bad verifier (which would delete the code
     * before the real client's exchange arrived).
     *
     * @return array{userId: int, challenge: ?string, secondFactorFailures: int}|null
     */
    public function peekCode(string $rawCode): ?array
    {
        $data = $this->payload($this->key($rawCode));

        if ($data === null) {
            return null;
        }

        return [
            'userId' => (int) $data['userId'],
            'challenge' => $data['challenge'] ?? null,
            'secondFactorFailures' => (int) ($data['secondFactorFailures'] ?? 0),
        ];
    }

    /**
     * Give a code whose user still owes a second factor SECOND_FACTOR_TTL_SECONDS
     * from now. Only once per code: the clock restarts for the app that proved
     * PKCE, not for every retry. False when the code is unknown or already extended.
     */
    public function extendForSecondFactor(string $rawCode): bool
    {
        $key = $this->key($rawCode);

        // Same per-code lock as consumeCode(): the payload is re-read inside it, so
        // a code consumed meanwhile is never written back into existence.
        return $this->withCodeLock($key, false, function () use ($key): bool {
            $data = $this->payload($key);

            if ($data === null || ! empty($data['secondFactorExtended'])) {
                return false;
            }

            $data['secondFactorExtended'] = true;
            $data['expiresAt'] = time() + self::SECOND_FACTOR_TTL_SECONDS;
            $this->store($key, $data);

            return true;
        });
    }

    public const SECOND_FACTOR_OK = 'ok';

    public const SECOND_FACTOR_WRONG = 'wrong';

    public const SECOND_FACTOR_BURNED = 'burned';

    public const SECOND_FACTOR_REFUSED = 'refused';

    /**
     * Check a second-factor code against this one-time code and settle the
     * outcome in ONE locked step: $verify runs only while this request holds
     * the code's lock, so concurrent exchanges can't each spend a free guess
     * and the attempt limit holds.
     *
     *  - OK: the second factor verified and the one-time code is consumed right
     *    here (never separately), so a replay with the same code finds nothing.
     *  - WRONG: counted against the code, which keeps its remaining lifetime.
     *  - BURNED: unknown, expired, or this wrong code reached
     *    MAX_SECOND_FACTOR_ATTEMPTS; the code is gone and the sign-in starts over.
     *  - REFUSED: another exchange holds the code's lock right now; $verify did
     *    not run and nothing was counted. Callers reject, they don't wait.
     *
     * @param  callable(): bool  $verify
     */
    public function checkSecondFactor(string $rawCode, callable $verify): string
    {
        $key = $this->key($rawCode);

        return $this->withCodeLock($key, self::SECOND_FACTOR_REFUSED, function () use ($key, $verify): string {
            $data = $this->payload($key);

            if ($data === null) {
                return self::SECOND_FACTOR_BURNED;
            }

            if ($verify()) {
                Cache::forget($key);

                return self::SECOND_FACTOR_OK;
            }

            $count = (int) ($data['secondFactorFailures'] ?? 0) + 1;

            if ($count >= self::MAX_SECOND_FACTOR_ATTEMPTS) {
                Cache::forget($key);

                return self::SECOND_FACTOR_BURNED;
            }

            $data['secondFactorFailures'] = $count;
            $this->store($key, $data);

            return self::SECOND_FACTOR_WRONG;
        });
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

        return $this->withCodeLock($key, false, function () use ($key): bool {
            $existed = $this->payload($key) !== null;
            Cache::forget($key);

            return $existed;
        });
    }

    /**
     * Run a read-modify-write on one code under its non-blocking cache lock.
     *
     * Fail CLOSED: without atomic locks we cannot guarantee single-use or an
     * honest attempt count, so refuse rather than fall back to a racy write
     * (which would reintroduce the double-mint window). All default Leantime
     * stores implement LockProvider; a store here that doesn't is a
     * misconfiguration worth surfacing loudly, not degrading to. The loser of a
     * concurrent attempt gets $refused immediately instead of waiting.
     *
     * @template T
     *
     * @param  T  $refused  Returned when no lock can be taken.
     * @param  callable(): T  $operation
     * @return T
     */
    private function withCodeLock(string $key, mixed $refused, callable $operation): mixed
    {
        if (! Cache::getStore() instanceof LockProvider) {
            Log::error('OidcMobileCode: cache store does not support atomic locks; refusing the mobile SSO code operation. Configure a lock-capable cache store (file/redis/memcached/database).');

            return $refused;
        }

        $lock = Cache::lock($key.'.lock', self::LOCK_SECONDS);

        if (! $lock->get()) {
            return $refused;
        }

        try {
            return $operation();
        } finally {
            $lock->release();
        }
    }

    /**
     * The stored payload, or null when missing, malformed or past its own
     * expiresAt (the cache TTL is the same deadline; this check keeps a store
     * with coarse TTLs honest).
     *
     * @return array<string, mixed>|null
     */
    private function payload(string $key): ?array
    {
        $data = Cache::get($key);

        if (! is_array($data) || ! isset($data['userId'])) {
            return null;
        }

        if (isset($data['expiresAt']) && (int) $data['expiresAt'] <= time()) {
            return null;
        }

        return $data;
    }

    /**
     * Write the payload with a cache TTL matching its expiresAt (at least one
     * second, so a re-store at the deadline still lands and then expires).
     *
     * @param  array<string, mixed>  $data
     */
    private function store(string $key, array $data): void
    {
        $ttl = max(1, (int) $data['expiresAt'] - time());

        Cache::put($key, $data, $ttl);
    }

    private function key(string $rawCode): string
    {
        return self::KEY_PREFIX.hash('sha256', $rawCode);
    }
}
