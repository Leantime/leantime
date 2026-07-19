<?php

namespace Leantime\Core\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\ApiRequest;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Api\Services\Api;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiRateLimiter
 *
 * This class is responsible for rate limiting requests, login requests and api requests
 */
class RequestRateLimiter
{
    use DispatchesEvents;

    protected RateLimiter $limiter;

    protected Environment $config;

    /**
     * __construct
     * Constructor method for the class.
     *
     * @param  RateLimiter  $limiter  The RateLimiter object to be initialized.
     * @return void.
     */
    public function __construct(Environment $config, RateLimiter $limiter)
    {
        $this->limiter = $limiter;
        $this->config = $config;
    }

    /**
     * Handle the incoming request.
     *
     * @param  IncomingRequest  $request  The incoming request object.
     * @param  Closure  $next  The next middleware closure.
     * @return Response The response object.
     *
     * @throws BindingResolutionException
     */
    public function handle(IncomingRequest $request, Closure $next): Response
    {

        if (! session('isInstalled')) {
            return $next($request);
        }

        // Normalize once: the Frontcontroller resolves controller classes case-insensitively, so
        // /Users/NewUser and /auth/Login reach the same controllers as their lowercase forms. Match
        // on the lowercased route so a mixed-case path can't slip past the login or signup limiter.
        $route = strtolower($request->getCurrentRoute());

        $isLoginRoute = $route === 'auth.login';

        // Abuse-sensitive POSTs: self-serve workspace signup and user invites. These send email and
        // provision resources, so the web form gets a tight per-IP budget (invite-spam abuse). The
        // JSON-RPC invite path (an ApiRequest) is NOT caught here — it is an API request throttled at
        // the API budget, and its real backstop is the per-user/per-tenant cap in
        // Users::invitesRateLimited(), which is entry-point-agnostic.
        $isSignupPost = in_array($route, ['accounts.register', 'accounts.newteam', 'users.newuser'], true)
            && $request->isMethod('POST');

        // Only check rate limits for login page, signup/invite posts, api calls, and the MCP endpoint
        if (! $isLoginRoute && ! $isSignupPost && ! $request->isApiOrCronRequest() && ! $request->isMcpRequest()) {
            return $next($request);
        }

        // Configurable rate limits
        $rateLimitGeneral = $this->config->ratelimitGeneral ?? 10000;
        $rateLimitApi = $this->config->ratelimitApi ?? 100;
        $rateLimitAuth = $this->config->ratelimitAuth ?? 20;
        $rateLimitMcp = $this->config->ratelimitMcp ?? 300;
        $rateLimitSignup = $this->config->ratelimitSignup ?? 5;

        if (config('app.debug')) {
            $rateLimitGeneral = 999999999;
            $rateLimitApi = 999999999;
            $rateLimitAuth = 999999999;
            $rateLimitMcp = 999999999;
            $rateLimitSignup = 999999999;
        }

        // Key
        // Key lives in domain namespace already
        $keyModifier = '0';
        if (session()->exists('userdata')) {
            $keyModifier = session('userdata.id');
        }

        $key = 'ratelimit-'.($request->getClientIp()).'-'.$keyModifier;

        // General Limit per minute
        $limit = $rateLimitGeneral;

        // API Routes Limit
        if ($request instanceof ApiRequest) {
            $apiKey = '';
            // $key = app()->make(Api::class)->getAPIKeyUser($apiKey);
            $limit = $rateLimitApi;
        }

        // MCP endpoint gets its own (higher) budget: agentic LLM clients legitimately burst
        // many parallel tool calls per conversation turn, which the API limit would choke on.
        if ($request->isMcpRequest()) {
            $limit = $rateLimitMcp;
        }

        if ($isSignupPost) {
            $limit = $rateLimitSignup;
            // Strictly per-IP: the signup form is unauthenticated (no session user id), and pinning
            // to IP alone stops one host from cycling sessions to widen its budget.
            $key = 'ratelimit-'.($request->getClientIp()).':signup';
        }

        if ($isLoginRoute) {
            $limit = $rateLimitAuth;
            $key = $key.':loginAttempts';

        }

        $key = self::dispatchFilter(
            'rateLimitKey',
            $key,
            [
                'bootloader' => $this,
            ],
        );

        $limit = self::dispatchFilter(
            'rateLimit',
            $limit,
            [
                'bootloader' => $this,
                'key' => $key,
            ],
        );

        if ($this->limiter->tooManyAttempts($key, $limit)) {
            Log::warning('too many requests per minute: '.$key);

            return new Response(
                json_encode(['error' => 'Too many requests per minute.']),
                Response::HTTP_TOO_MANY_REQUESTS,
                $this->getHeaders($key, (int) $limit),
            );
        }

        $this->limiter->hit($key, 60);

        return $next($request);
    }

    /**
     * Get rate limiter headers for response.
     */
    private function getHeaders(string $key, int $limit): array
    {
        return [
            'X-RateLimit-Remaining' => $this->limiter->retriesLeft($key, $limit),
            'X-RateLimit-Retry-After' => $this->limiter->availableIn($key),
            'X-RateLimit-Limit' => $this->limiter->attempts($key),
            'Retry-After' => $this->limiter->availableIn($key),
        ];
    }
}
