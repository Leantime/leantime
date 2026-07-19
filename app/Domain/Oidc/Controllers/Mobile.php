<?php

namespace Leantime\Domain\Oidc\Controllers;

use Illuminate\Support\Facades\RateLimiter;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;
use Leantime\Domain\Oidc\Services\OidcMobileCode;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mobile SSO bridge — the code→token exchange.
 *
 * POST /oidc/mobile/exchange. Public (no session/cookie): the validated,
 * single-use one-time code IS the authorization. See OidcMobileCode.
 *
 * This route must be allow-listed in AuthCheck::$publicActions as 'oidc.mobile'.
 */
class Mobile extends Controller
{
    /** Per-IP cap on exchange attempts per minute — throttles code/verifier guessing. */
    private const MAX_ATTEMPTS_PER_MINUTE = 10;

    /** Mobile SSO bearer lifetime. Deliberately NOT non-expiring; a lost device's
     *  token self-expires, and it can be revoked early via AccessTokenRepository::deleteToken. */
    private const TOKEN_TTL_DAYS = 30;

    private OidcMobileCode $codes;

    private AccessTokenRepository $tokens;

    private UserRepository $userRepo;

    private IncomingRequest $request;

    public function init(
        OidcMobileCode $codes,
        AccessTokenRepository $tokens,
        UserRepository $userRepo,
        IncomingRequest $request
    ): void {
        $this->codes = $codes;
        $this->tokens = $tokens;
        $this->userRepo = $userRepo;
        $this->request = $request;
    }

    /**
     * Exchange a one-time code for a bearer token.
     *
     * Reached at /oidc/mobile/exchange (segment[2] "exchange" → this method).
     * POST only — GET is refused so secrets can't be exchanged from a query
     * string (URLs land in access logs; POST bodies don't).
     */
    public function exchange(array $params): Response
    {
        // Frontcontroller resolves methods by URL segment regardless of verb;
        // enforce POST here so `?code=...&code_verifier=...` on a GET is
        // rejected before we touch the code store.
        if ($this->request->getMethod() !== 'POST') {
            return new JsonResponse(['error' => 'method_not_allowed'], 405, ['Allow' => 'POST']);
        }

        // Per-IP throttle: this endpoint is public (allow-listed in AuthCheck) and
        // returns distinct 400/401 codes, so an unauthenticated caller could probe
        // codes/verifiers. Even with <=60s single-use codes, cap the attempt rate.
        $throttleKey = 'oidc.mobile.exchange:'.$this->request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS_PER_MINUTE)) {
            return new JsonResponse(
                ['error' => 'too_many_requests'],
                429,
                ['Retry-After' => (string) RateLimiter::availableIn($throttleKey)]
            );
        }
        RateLimiter::hit($throttleKey, 60);

        // Secrets are read from the POST BODY only (->post()), never the query
        // string — URLs land in access logs, request bodies don't. A ?code=... in
        // the URL is ignored; $params (the merged bag) is intentionally not used.
        $code = $this->bodyParam('code');
        if ($code === '') {
            return new JsonResponse(['error' => 'missing_code'], 400);
        }

        // Peek (non-destructive) so a bad verifier from a scheme-hijacker
        // can't burn the code before the legitimate app's exchange arrives.
        // The code is only consumed after PKCE + user validation succeed.
        $data = $this->codes->peekCode($code);
        if ($data === null) {
            // Unknown, expired, or already-used code — all indistinguishable to
            // the caller on purpose.
            return new JsonResponse(['error' => 'invalid_code'], 401);
        }

        // PKCE: the code was bound to a code_challenge at login. Require the
        // matching verifier so a code intercepted from the app-scheme redirect
        // is useless without the secret the app kept and never put in a URL.
        $verifier = $this->bodyParam('code_verifier');
        if (! $this->pkceMatches($data['challenge'] ?? null, $verifier)) {
            return new JsonResponse(['error' => 'invalid_verifier'], 401);
        }

        // The code came from a completed OIDC auth (+ verified PKCE), so minting
        // for this user is authorized. Confirm the user still exists FIRST — if
        // they were deleted between callback and exchange, minting would leave an
        // orphaned token row. Use the repository directly (the AccessToken
        // service gates on an active session, which this cookieless request
        // doesn't have).
        $userId = (int) $data['userId'];
        $user = $this->userRepo->getUser($userId);
        if (! is_array($user) || empty($user)) {
            return new JsonResponse(['error' => 'invalid_user'], 401);
        }

        // All checks passed — atomically burn the code. consumeCode() returns
        // false if a concurrent exchange already consumed it, so only the winner
        // of that race mints (no double-mint from one single-use code).
        if (! $this->codes->consumeCode($code)) {
            return new JsonResponse(['error' => 'invalid_code'], 401);
        }

        // Mint a 'mobile-sso' bearer with an explicit TTL (see TOKEN_TTL_DAYS) so
        // it isn't valid forever. Scope stays ['*'] — the mobile app is a full
        // API client, same as the password-login token — but the TTL plus
        // AccessTokenRepository::deleteToken give expiry and revocation.
        $token = $this->tokens->createToken(
            $userId,
            'mobile-sso',
            ['*'],
            now()->addDays(self::TOKEN_TTL_DAYS)
        );

        return new JsonResponse([
            'token' => $token['token'],
            'user' => $this->safeUser($user, $userId),
        ]);
    }

    /**
     * Read a request value from the POST body ONLY (never the query string), so
     * the one-time code + verifier can't be supplied via a logged URL.
     */
    private function bodyParam(string $key): string
    {
        $value = $this->request->post($key);

        return is_string($value) ? trim($value) : '';
    }

    /**
     * PKCE S256 check: base64url(sha256(verifier)) must equal the stored
     * challenge. Every mobile login sends a challenge, so a code with no bound
     * challenge — or a missing/mismatched verifier — is rejected.
     */
    private function pkceMatches(?string $challenge, string $verifier): bool
    {
        if (empty($challenge) || $verifier === '') {
            return false;
        }

        $computed = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        return hash_equals($challenge, $computed);
    }

    /**
     * Return ONLY safe identity fields. Never the password hash, 2FA seed, or
     * session/reset tokens (cf. the users.getUser credential-dump incident) —
     * the full zp_user row carries all of those.
     */
    private function safeUser(array $user, int $userId): array
    {
        return [
            'id' => $userId,
            'firstname' => $user['firstname'] ?? '',
            'lastname' => $user['lastname'] ?? '',
            'username' => $user['username'] ?? '',
        ];
    }
}
