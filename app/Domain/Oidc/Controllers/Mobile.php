<?php

namespace Leantime\Domain\Oidc\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;
use Leantime\Domain\Oidc\Repositories\OidcMobileCode;
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
        $code = $this->readCode($params);
        if ($code === '') {
            return new JsonResponse(['error' => 'missing_code'], 400);
        }

        $userId = $this->codes->consumeCode($code);
        if ($userId === null) {
            // Unknown, expired, or already-used code — all indistinguishable to
            // the caller on purpose.
            return new JsonResponse(['error' => 'invalid_code'], 401);
        }

        // The code came from a completed OIDC auth, so minting for this user is
        // authorized. Use the repository directly (the AccessToken service gates
        // on an active session, which this cookieless request doesn't have).
        $token = $this->tokens->createToken($userId, 'mobile-sso');

        $user = $this->userRepo->getUser($userId);

        return new JsonResponse([
            'token' => $token['token'],
            'user' => $this->safeUser(is_array($user) ? $user : [], $userId),
        ]);
    }

    /**
     * Read the code from the JSON body (mobile sends application/json). Falls
     * back to merged request params for form-encoded callers.
     */
    private function readCode(array $params): string
    {
        if (! empty($params['code']) && is_string($params['code'])) {
            return $params['code'];
        }

        $raw = file_get_contents('php://input');
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && ! empty($decoded['code']) && is_string($decoded['code'])) {
                return $decoded['code'];
            }
        }

        return '';
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
