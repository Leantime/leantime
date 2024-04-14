<?php

namespace Leantime\Core\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Leantime\Core\ApiRequest;
use Leantime\Core\Eventhelpers;
use Leantime\Core\Frontcontroller;
use Leantime\Core\IncomingRequest;
use Leantime\Core\Middleware\Request;
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
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle the incoming request.
     *
     * @param IncomingRequest $request The incoming request object.
     * @param Closure         $next    The next middleware closure.
     * @return Response The response object.
     */
    public function handle(IncomingRequest $request, Closure $next): Response
    {

        //Key
        $key = $request->getClientIp();

        //General Limit per minute
        $limit = 1000;

        //API Routes Limit
        if ($request instanceof ApiRequest) {
            $apiKey = "";
            $key = app()->make(ApiRequest::class)->getAPIKeyUser($apiKey);
            $limit = 10;
        }

        $route = Frontcontroller::getCurrentRoute();

        if ($route == "auth.login") {
            $limit = 20;
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
            return new Response(json_encode(['error' => 'Too many requests per minute.']), Response::HTTP_TOO_MANY_REQUESTS);
        }
        $this->limiter->hit($key, 60);

        return $next($request);
    }
}
