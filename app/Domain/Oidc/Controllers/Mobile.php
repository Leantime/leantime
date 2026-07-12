<?php

namespace Leantime\Domain\Oidc\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;
use Leantime\Domain\Oidc\Services\OidcMobileCode;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mobile SSO bridge — the code→token exchange.
 *
 * POST /oidc/mobile/exchange. Public (no session/cookie): the validated,
 * single-use one-time code IS the authorization. See
 * docs/backend-mobile-auth-bridge-plan.md and OidcMobileCode.
 *
 * This route must be allow-listed in AuthCheck::$publicActions as 'oidc.mobile'.
 */
class Mobile extends Controller
{
    private OidcMobileCode $codes;

    private AccessTokenRepository $tokens;

    private UserRepository $userRepo;

    public function init(
        OidcMobileCode $codes,
        AccessTokenRepository $tokens,
        UserRepository $userRepo
    ): void {
        $this->codes = $codes;
        $this->tokens = $tokens;
        $this->userRepo = $userRepo;
    }

    /**
     * Exchange a one-time code for a bearer token.
     *
     * Reached at /oidc/mobile/exchange (segment[2] "exchange" → this method).
     */
    public function exchange(array $params): Response
    {
        // $params is the framework's merged request params (GET + form body); the
        // app POSTs code + code_verifier form-encoded, so no manual body parsing.
        $code = $this->stringParam($params, 'code');
        if ($code === '') {
            return new JsonResponse(['error' => 'missing_code'], 400);
        }

        $data = $this->codes->consumeCode($code);
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
        // for this user is authorized. Use the repository directly (the
        // AccessToken service gates on an active session, which this cookieless
        // request doesn't have).
        $userId = (int) $data['userId'];
        $token = $this->tokens->createToken($userId, 'mobile-sso');

        $user = $this->userRepo->getUser($userId);

        return new JsonResponse([
            'token' => $token['token'],
            'user' => $this->safeUser(is_array($user) ? $user : [], $userId),
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
