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
use Leantime\Domain\Users\Repositories\Users as UserRepository;
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
     * @return void
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

        $action = $this->normalizedAction($request);

        $isLoginRoute = $action === 'auth.login';

        // Abuse-sensitive POSTs: self-serve workspace signup and user invites. These send email and
        // provision resources, so the web form gets a tight per-IP budget (invite-spam abuse). The
        // JSON-RPC invite path (an ApiRequest) is NOT caught here — it is an API request throttled at
        // the API budget, and its real backstop is the per-user/per-tenant cap in
        // Users::invitesRateLimited(), which is entry-point-agnostic.
        $isSignupPost = in_array($action, ['accounts.register', 'accounts.newteam', 'users.newuser'], true)
            && $request->isMethod('POST');

        // Password reset: both requesting a reset email and submitting a new password.
        $isPasswordResetPost = $action === 'auth.resetpw' && $request->isMethod('POST');

        // Second-factor code entry: a 6-digit code must not be brute-forceable.
        $isTwoFAVerifyPost = $action === 'twofa.verify' && $request->isMethod('POST');

        // Only check rate limits for login page, signup/invite posts, reset + 2FA posts, api calls, and the MCP endpoint
        if (
            ! $isLoginRoute
            && ! $isSignupPost
            && ! $isPasswordResetPost
            && ! $isTwoFAVerifyPost
            && ! $request->isApiOrCronRequest()
            && ! $request->isMcpRequest()
        ) {
            return $next($request);
        }

        // Configurable rate limits
        $rateLimitGeneral = $this->config->ratelimitGeneral ?? 10000;
        $rateLimitApi = $this->config->ratelimitApi ?? 100;
        $rateLimitAuth = $this->config->ratelimitAuth ?? 20;
        $rateLimitMcp = $this->config->ratelimitMcp ?? 300;
        $rateLimitSignup = $this->config->ratelimitSignup ?? 5;
        $rateLimitPasswordReset = $this->config->ratelimitPasswordReset ?? 5;
        $rateLimitTwoFA = $this->config->ratelimitTwofa ?? 5;

        if (config('app.debug')) {
            $rateLimitGeneral = 999999999;
            $rateLimitApi = 999999999;
            $rateLimitAuth = 999999999;
            $rateLimitMcp = 999999999;
            $rateLimitSignup = 999999999;
            $rateLimitPasswordReset = 999999999;
            $rateLimitTwoFA = 999999999;
        }

        // Key
        // Key lives in domain namespace already
        $keyModifier = '0';
        if (session()->exists('userdata')) {
            $keyModifier = session('userdata.id');
        }

        $clientIp = $request->getClientIp();
        $key = 'ratelimit-'.$clientIp.'-'.$keyModifier;

        // General Limit per minute
        $limit = $rateLimitGeneral;
        $decaySeconds = 60;

        // Additional buckets that must ALSO have budget left (e.g. per-account counters that a
        // client can't widen by rotating its IP address or session).
        $extraBuckets = [];

        // API Routes Limit
        if ($request instanceof ApiRequest) {
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
            $key = 'ratelimit-'.$clientIp.':signup';
        }

        if ($isLoginRoute) {
            $limit = $rateLimitAuth;
            // Per IP only: a session (user id) suffix would let a client widen its budget by
            // rotating sessions.
            $key = 'ratelimit-'.$clientIp.':loginAttempts';

            // Per-account budget so a distributed guessing run against one user is throttled too.
            // Only for usernames that belong to an account, keyed by its id: unknown usernames are
            // bounded by the per-IP bucket and must not create a cache entry each.
            $accountId = $request->isMethod('POST') ? $this->loginAccountId($request->input('username')) : null;
            if ($accountId !== null) {
                $extraBuckets[] = [
                    'key' => 'ratelimit-login-user-'.$accountId,
                    'limit' => $rateLimitAuth,
                    'decay' => 60,
                ];
            }
        }

        if ($isPasswordResetPost) {
            // Strictly per-IP (the form is unauthenticated); 10-minute window.
            $limit = $rateLimitPasswordReset;
            $key = 'ratelimit-'.$clientIp.':passwordReset';
            $decaySeconds = 600;
        }

        if ($isTwoFAVerifyPost) {
            // Per user, independent of IP and session: rotating either must not buy more guesses.
            $limit = $rateLimitTwoFA;
            $key = 'ratelimit-2fa-user-'.$keyModifier;
            $decaySeconds = 300;
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

        $buckets = array_merge([['key' => $key, 'limit' => $limit, 'decay' => $decaySeconds]], $extraBuckets);

        foreach ($buckets as $bucket) {
            if ($this->limiter->tooManyAttempts($bucket['key'], $bucket['limit'])) {
                Log::warning('too many requests: '.$bucket['key']);

                return new Response(
                    json_encode(['error' => 'Too many requests. Please try again later.']),
                    Response::HTTP_TOO_MANY_REQUESTS,
                    $this->getHeaders($bucket['key'], (int) $bucket['limit']),
                );
            }
        }

        foreach ($buckets as $bucket) {
            $this->limiter->hit($bucket['key'], $bucket['decay']);
        }

        return $next($request);
    }

    /**
     * Resolve the account a login attempt targets, or null for an empty/unknown username.
     */
    private function loginAccountId(mixed $username): ?int
    {
        if (! is_string($username) || trim($username) === '') {
            return null;
        }

        $user = app(UserRepository::class)->getUserByEmail(trim($username));

        return is_array($user) && ! empty($user['id']) ? (int) $user['id'] : null;
    }

    /**
     * Reduce the request path to its lowercase "module.action" pair.
     *
     * The Frontcontroller resolves controllers case-insensitively and treats any further path
     * segments as parameters, so /Auth/Login, /auth/login/x and //auth//login all reach the login
     * controller. Matching on the normalized pair keeps those variants inside the limiter.
     */
    public function normalizedAction(IncomingRequest $request): string
    {
        $segments = array_values(array_filter(
            explode('.', strtolower((string) $request->getCurrentRoute())),
            fn (string $segment) => $segment !== ''
        ));

        return implode('.', array_slice($segments, 0, 2));
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
