<?php

namespace Leantime\Domain\Oauth\Controllers;

use Illuminate\Cache\RateLimiter;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Oauth\Services\OAuthServer;
use Symfony\Component\HttpFoundation\Response;

/**
 * POST /oauth/token (public): exchange an authorization code or refresh token for tokens.
 *
 * Failed exchanges count against the same per-IP failed-authentication budget as API keys and
 * Bearer tokens (LEAN_RATELIMIT_AUTH), so the endpoint can't be used to guess codes.
 */
class Token
{
    public function __construct(
        private OAuthServer $oauthServer,
        private RateLimiter $rateLimiter,
        private Environment $config,
    ) {}

    public function issue(IncomingRequest $request): Response
    {
        $failedAttemptsKey = 'api-auth-failures:'.$request->getClientIp();

        if ($this->rateLimiter->tooManyAttempts($failedAttemptsKey, (int) ($this->config->ratelimitAuth ?? 20))) {
            return new Response(
                (string) json_encode(['error' => 'slow_down', 'error_description' => 'Too many failed attempts. Try again later.']),
                Response::HTTP_TOO_MANY_REQUESTS,
                ['Content-Type' => 'application/json', 'Retry-After' => '60']
            );
        }

        $response = $this->oauthServer->issueToken($request);

        if (in_array($response->getStatusCode(), [400, 401], true)) {
            $this->rateLimiter->hit($failedAttemptsKey, 60);
        }

        return $response;
    }
}
