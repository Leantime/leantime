<?php

namespace Leantime\Domain\Oauth\Controllers;

use Illuminate\Cache\RateLimiter;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Oauth\Services\OAuthServer;
use Symfony\Component\HttpFoundation\Response;

/**
 * POST /oauth/register (public): RFC 7591 dynamic client registration. This is how Claude and
 * ChatGPT register themselves; nobody creates apps by hand.
 *
 * The endpoint is unauthenticated and writes a row, so it has a per-IP hourly budget
 * (LEAN_RATELIMIT_OAUTH_REGISTER), and clients that never complete a sign-in are purged daily.
 * What may be registered is decided in {@see OAuthServer::registerClient()}.
 */
class Register
{
    public function __construct(
        private OAuthServer $oauthServer,
        private RateLimiter $rateLimiter,
        private Environment $config,
    ) {}

    public function post(IncomingRequest $request): Response
    {
        $limiterKey = 'oauth-register:'.$request->getClientIp();

        if ($this->rateLimiter->tooManyAttempts($limiterKey, (int) ($this->config->ratelimitOauthRegister ?? 10))) {
            return new Response(
                (string) json_encode(['error' => 'slow_down', 'error_description' => 'Too many client registrations. Try again later.']),
                Response::HTTP_TOO_MANY_REQUESTS,
                ['Content-Type' => 'application/json', 'Retry-After' => '3600']
            );
        }

        $this->rateLimiter->hit($limiterKey, 3600);

        $metadata = $request->isJson() ? $request->json()->all() : $request->request->all();
        $registration = $this->oauthServer->registerClient($metadata);

        return new Response(
            (string) json_encode($registration['body'], JSON_UNESCAPED_SLASHES),
            $registration['status'],
            ['Content-Type' => 'application/json', 'Cache-Control' => 'no-store']
        );
    }
}
