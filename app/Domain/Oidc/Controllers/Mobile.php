<?php

namespace Leantime\Domain\Oidc\Controllers;

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

        // $params is the framework's merged request params (GET + form body); the
        // app POSTs code + code_verifier form-encoded, so no manual body parsing.
        $code = $this->stringParam($params, 'code');
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
        $verifier = $this->stringParam($params, 'code_verifier');
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

        // All checks passed — burn the code (single-use) and mint the token.
        $this->codes->consumeCode($code);
        $token = $this->tokens->createToken($userId, 'mobile-sso');

        return new JsonResponse([
            'token' => $token['token'],
            'user' => $this->safeUser($user, $userId),
        ]);
    }

    private function stringParam(array $params, string $key): string
    {
        return isset($params[$key]) && is_string($params[$key]) ? trim($params[$key]) : '';
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
