<?php

namespace Leantime\Core\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Leantime\Core\ApiRequest;
use Leantime\Core\Environment;
use Leantime\Core\Eventhelpers;
use Leantime\Core\Frontcontroller;
use Leantime\Core\IncomingRequest;
use Leantime\Core\Middleware\Request;
use Leantime\Domain\Api\Services\Api;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiRateLimiter
 *
 * This class is responsible for rate limiting requests, login requests and api requests
 */
class RequestRateLimiter
{
    use Eventhelpers;

    protected RateLimiter $limiter;

    /**
     * __construct
     * Constructor method for the class.
     *
     * @param RateLimiter $limiter The RateLimiter object to be initialized.
     * @return void.
     */
    public function __construct()
    {
        app()->singleton(RateLimiter::class, fn($app)=> new RateLimiter(Cache::store("installation")));
        $this->limiter = app()->make(RateLimiter::class);
    }

    /**
     * Handle the incoming request.
     *
     * @param IncomingRequest $request The incoming request object.
     * @param Closure $next The next middleware closure.
     * @return Response The response object.
     * @throws BindingResolutionException
     */
    public function handle(IncomingRequest $request, Closure $next): Response
    {
        //Configurable rate limits
        $LEAN_RATELIMIT_GENERAL = app()->make(Environment::class)->get('LEAN_RATELIMIT_GENERAL') ?? 1000;
        $LEAN_RATELIMIT_API = app()->make(Environment::class)->get('LEAN_RATELIMIT_API') ?? 10;
        $LEAN_RATELIMIT_AUTH = app()->make(Environment::class)->get('LEAN_RATELIMIT_AUTH') ?? 20;

        //Key
        $key = $request->getClientIp();

        //General Limit per minute
        $limit = $LEAN_RATELIMIT_GENERAL;

        //API Routes Limit
        if ($request instanceof ApiRequest) {
            $apiKey = "";
            $key = app()->make(Api::class)->getAPIKeyUser($apiKey);
            $limit = $LEAN_RATELIMIT_API;
        }

        $route = Frontcontroller::getCurrentRoute();

        if ($route == "auth.login") {
            $limit = $LEAN_RATELIMIT_AUTH;
            $key = $key . ".loginAttempts";
        }

        $key = self::dispatch_filter(
            "rateLimit",
            $key,
            [
                "bootloader" => $this,
            ],
        );

        $limit = self::dispatch_filter(
            "rateLimit",
            $limit,
            [
                "bootloader" => $this,
                "key" => $key,
            ],
        );
        if ($this->limiter->tooManyAttempts($key, $limit)) {
            error_log("too many requests per minute: " . $key);
            return new Response(
                json_encode(['error' => 'Too many requests per minute.']),
                Response::HTTP_TOO_MANY_REQUESTS,
                $this->getHeaders($key, $limit),
            );
        }

        $this->limiter->hit($key, 60);

        return $next($request);
    }


    /**
     * Get rate limiter headers for response.
     *
     * @param string $key
     *
     * @param string $limit
     *
     * @return array
     */
    private function getHeaders(string $key, string $limit): array
    {
        return [
            'X-RateLimit-Remaining' => $this->limiter->retriesLeft($key, $limit),
            'X-RateLimit-Retry-After' => $this->limiter->availableIn($key),
            'X-RateLimit-Limit' => $this->limiter->attempts($key),
            'X-RateLimit-type' => $key,
        ];
    }
}
