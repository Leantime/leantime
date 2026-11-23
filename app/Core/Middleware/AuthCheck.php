<?php

namespace Leantime\Core\Middleware;

use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Leantime\Core\Auth\OAuth\OAuthBearerAuthenticator;
use Leantime\Core\Auth\OAuth\OAuthServiceProvider;
use Leantime\Core\Auth\OAuth\ProtectedResources;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\ApiRequest;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Auth\Guards\WebGuard;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthCheck
{
    use DispatchesEvents;

    /**
     * Public actions
     */
    private array $publicActions = [
        'auth.login',
        'auth.resetPw',
        'auth.userInvite',
        'install',
        'install.index',
        'install.update',
        'errors.error404',
        'errors.error500',
        'api.i18n',
        'api.static-asset',
        'calendar.ical',
        'oidc.login',
        'oidc.callback',
        'oidc.mobile',
        'status',
        'status.index',
        'cron.run',
        'auth.callback',
        'auth.redirect',
    ];

    public function __construct(
        protected Environment $config,
        protected Auth $auth,
        protected AuthManager $authManager,
    ) {
        $this->publicActions = self::dispatchFilter('publicActions', $this->publicActions, ['bootloader' => $this]);
    }

    /**
     * Handle the request
     */
    public function handle(IncomingRequest $request, Closure $next): Response
    {
        // API, cron and MCP endpoints must be addressed by their canonical path. A path that only
        // resolves to them after decoding/normalization (e.g. /%61pi/jsonrpc, /api//jsonrpc) is
        // rejected outright so classification and routing can never disagree. Static assets are
        // exempt: they are plain files whose names may legitimately be percent-encoded.
        if ($this->mustUseCanonicalPath($request) && $request->hasNonCanonicalPath()) {
            return new Response(json_encode(['error' => 'Invalid request path']), Response::HTTP_BAD_REQUEST);
        }

        if ($this->isPublicController($request->getCurrentRoute())
            || $this->isPublicOAuthEndpoint($request)
            || $this->isPublicWellKnownPath($request)) {
            return $next($request);
        }

        // Throttle credential brute force on token-authenticated endpoints (/api, /mcp). This
        // must live here rather than in RequestRateLimiter: that middleware runs AFTER AuthCheck,
        // so a failed-auth 401 short-circuits the pipeline before any request limit is counted.
        if ($request instanceof ApiRequest && $this->tooManyFailedAuthAttempts($request)) {
            return new Response(
                json_encode(['error' => 'Too many failed authentication attempts. Try again later.']),
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }

        $loginRedirect = self::dispatch_filter('loginRoute', 'auth.login', ['request' => $request]);

        if ($request instanceof ApiRequest) {
            self::dispatchEvent('before_api_request', ['application' => app()], 'leantime.core.middleware.apiAuth.handle');
        }

        $authCheckResponse = $this->authenticate($request, array_keys($this->config->get('auth.guards')), $loginRedirect, $next);

        // If auth fails either return json response or do the redirect
        if ($authCheckResponse !== true) {
            return $authCheckResponse;
        }

        self::dispatchEvent('logged_in', ['application' => $this]);

        return $next($request);
    }

    protected function authenticate($request, array $guards, $loginRedirect, $next)
    {
        if ($request->isApiOrCronRequest() || $request->isMcpRequest()) {
            return $this->authenticateApi($request, $guards);
        }

        return $this->authenticateWeb($request, $guards, $loginRedirect, $next);
    }

    protected function authenticateWeb(IncomingRequest $request, array $guards, string $loginRedirect, Closure $next): bool|Response
    {
        $authenticated = false;
        $response = null;

        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                $this->auth->shouldUse($guard);

                // Check two-factor authentication
                if (! $this->sessionTwoFASatisfied()) {
                    $this->rememberOAuthAuthorizeRequest($request);
                    $response = $this->redirectWithOrigin('twoFA.verify', $_GET['redirect'] ?? '', $request) ?: $next($request);
                } else {
                    $authenticated = true;
                }

                break;
            }
        }

        if (! $authenticated && ! $response) {
            if ($request instanceof ApiRequest) {
                $this->hitFailedAuthLimiter($request);
                $response = new Response(json_encode(['error' => 'Invalid API Key']), 401);
            } else {
                $this->rememberOAuthAuthorizeRequest($request);
                $response = $this->redirectWithOrigin($loginRedirect, $request->getRequestUri(), $request) ?: $next($request);
            }
        }

        return $authenticated ? true : $response;
    }

    /**
     * Authenticate a token-authenticated request (JSON-RPC, cron, MCP).
     *
     * The stateful web session guard is only honoured for same-origin XHR calls (the app's own
     * JavaScript) whose session has completed two-factor verification. Anything else — a top-level
     * navigation or cross-site form riding the session cookie, or a session still waiting on 2FA —
     * must authenticate with an API key or Bearer token.
     *
     * @return true|Response True when authenticated, otherwise the 401 response.
     */
    protected function authenticateApi(IncomingRequest $request, array $guards): bool|Response
    {
        $sessionAwaitingTwoFA = false;

        foreach ($guards as $guard) {
            try {
                $guardInstance = $this->auth->guard($guard);

                if ($guardInstance instanceof WebGuard) {
                    if (! $request->ajax()) {
                        continue;
                    }

                    if (! $this->sessionTwoFASatisfied()) {
                        $sessionAwaitingTwoFA = $sessionAwaitingTwoFA || $guardInstance->check();

                        continue;
                    }
                }

                if ($guardInstance->check()) {
                    $this->auth->shouldUse($guard);
                    $request->attributes->set(AuthenticateSession::TOKEN_AUTHENTICATED, $guard !== 'leantime');

                    $this->establishApiUserSession($request);

                    return true;
                }
            } catch (\Throwable $e) {
                // A guard that throws while evaluating this request type must not abort the chain;
                // keep trying the others, then fall through to the Bearer / 401 handling below.
                // Logged at debug, not warning: a misconfigured/disabled guard would throw on
                // every API request, so warning-level here would flood production logs for a
                // condition we recover from cleanly. Debug keeps it available when investigating.
                Log::debug('API auth guard "'.$guard.'" threw while evaluating the request', ['exception' => $e]);

                continue;
            }
        }

        // Bearer / personal-access-token fallback. Leantime mints plain Str::random tokens
        // (sha256-hashed in zp_access_tokens) — NOT Sanctum's {id}|{plaintext} format — so
        // Sanctum's guard never resolves them and Bearer auth 401s. Validate against the core
        // token store directly (the same path the McpServer + AuthUser provider use), so Bearer
        // auth works for the mobile app and AdvancedAuth integrators independent of Sanctum's
        // token format or the plugin. getUserByToken enforces expiry and returns the user row.
        // Use ApiRequest::getBearerToken(), not Laravel's $request->bearerToken(): the latter only
        // reads the plain `Authorization` header, which Apache does not expose to PHP's header bag
        // here (it lands in HTTP_AUTHORIZATION / REDIRECT_HTTP_AUTHORIZATION). getBearerToken()
        // checks those variants, so this fires where bearerToken() silently returned null.
        $bearer = method_exists($request, 'getBearerToken') ? $request->getBearerToken() : $request->bearerToken();

        // OAuth access tokens (JWTs), only on the endpoints that accept them (/mcp) and only with
        // the scope that endpoint requires. They never reach the personal access token lookup.
        $protectedResourcePath = OAuthServiceProvider::enabled() ? ProtectedResources::pathForRequest($request) : null;

        if (! empty($bearer) && $protectedResourcePath !== null && OAuthBearerAuthenticator::looksLikeAccessToken($bearer)) {
            $oauthUserId = app(OAuthBearerAuthenticator::class)->authenticate($bearer, ProtectedResources::scopeFor($protectedResourcePath));
            $oauthUser = $oauthUserId !== null ? app(\Leantime\Domain\Users\Services\Users::class)->getUser($oauthUserId) : null;

            if (is_array($oauthUser) && ! empty($oauthUser['id'])) {
                app(\Leantime\Domain\Api\Services\Api::class)->setApiUserSession($oauthUser, true);
                $request->attributes->set(AuthenticateSession::TOKEN_AUTHENTICATED, true);

                return true;
            }

            $this->hitFailedAuthLimiter($request);

            return $this->unauthorizedResponse($request, $protectedResourcePath, invalidToken: true);
        }

        if (! empty($bearer)) {
            $user = app(\Leantime\Domain\Auth\Services\Auth::class)->getUserByToken($bearer);

            if (is_array($user) && ! empty($user['id'])) {
                // Establish the Leantime user context the permission engine (and the rest of the
                // app) reads — session('userdata'). Deliberately NOT setting a request user
                // resolver: leaving $request->user() null lets AuthenticateSession bail instead of
                // calling viaRemember() on the non-session WebGuard, matching the x-api-key path.
                app(\Leantime\Domain\Api\Services\Api::class)->setApiUserSession($user, true);
                $request->attributes->set(AuthenticateSession::TOKEN_AUTHENTICATED, true);

                return true;
            }
        }

        // A logged-in browser session that has not finished 2FA, and that presented no API key or
        // Bearer token, is not a credential-guessing attempt; reject it without counting against
        // the per-IP failed-auth budget. Any attempted token is always counted.
        if ($sessionAwaitingTwoFA && ! $this->presentedApiCredential($request)) {
            return new Response(json_encode(['error' => 'Two-factor authentication required']), 401);
        }

        $this->hitFailedAuthLimiter($request);

        return $this->unauthorizedResponse($request, $protectedResourcePath, invalidToken: ! empty($bearer));
    }

    /**
     * The 401 for a token-authenticated endpoint. On an endpoint that accepts OAuth (/mcp) it
     * carries the RFC 9728 `WWW-Authenticate` challenge that points MCP clients to the OAuth
     * discovery document; that header is what starts the "Sign in" flow in Claude and ChatGPT.
     * `error="invalid_token"` tells a client holding an expired token to refresh it.
     */
    protected function unauthorizedResponse(IncomingRequest $request, ?string $protectedResourcePath, bool $invalidToken): Response
    {
        $response = new Response(json_encode(['error' => 'Unauthorized']), 401, ['Content-Type' => 'application/json']);

        if ($protectedResourcePath === null) {
            return $response;
        }

        $challenge = 'Bearer realm="leantime", resource_metadata="'.rtrim(BASE_URL, '/').'/.well-known/oauth-protected-resource/'.$protectedResourcePath.'"';
        $challenge .= ', scope="'.ProtectedResources::scopeFor($protectedResourcePath).'"';

        if ($invalidToken) {
            $challenge .= ', error="invalid_token"';
        }

        $response->headers->set('WWW-Authenticate', $challenge);

        return $response;
    }

    /**
     * Public OAuth endpoints: the discovery documents, dynamic client registration and the token
     * endpoint. (/oauth/authorize is not public: it needs a signed-in user.) The generic
     * {@see self::isPublicController()} can't express these: "/.well-known/…" starts with a dot,
     * which that check uses as its separator.
     */
    protected function isPublicOAuthEndpoint(IncomingRequest $request): bool
    {
        if (! OAuthServiceProvider::enabled()) {
            return false;
        }

        $segments = $request->normalizedSegments();
        $firstSegment = $segments[0] ?? '';
        $secondSegment = $segments[1] ?? '';

        if ($firstSegment === '.well-known') {
            return in_array($secondSegment, ['oauth-protected-resource', 'oauth-authorization-server'], true);
        }

        return $firstSegment === 'oauth' && count($segments) === 2 && in_array($secondSegment, ['token', 'register'], true);
    }

    /**
     * Whether the request is for exactly "/.well-known/{name}" and a plugin listed "well-known.{name}"
     * as public (the CalDAV plugin lists "well-known.caldav"). The name must be the whole second
     * segment, so "well-known.security.txt" opens "/.well-known/security.txt" but not
     * "/.well-known/security/txt", and nothing below a listed name is opened.
     */
    protected function isPublicWellKnownPath(IncomingRequest $request): bool
    {
        $segments = $request->segments();

        return count($segments) === 2
            && $segments[0] === '.well-known'
            && in_array('well-known.'.$segments[1], $this->publicActions, true);
    }

    /**
     * Remember an OAuth authorize request that is about to be bounced to the login or 2FA page,
     * so that whichever way the user signs in (password, 2FA, SSO) they land back on the consent
     * screen. Kept in the session rather than in ?redirect=, which loses the query string.
     */
    protected function rememberOAuthAuthorizeRequest(IncomingRequest $request): void
    {
        if (! OAuthServiceProvider::enabled() || ! $request->isMethod('GET')) {
            return;
        }

        if ($request->normalizedSegments() !== ['oauth', 'authorize']) {
            return;
        }

        $queryString = $request->getQueryString();

        app(\Leantime\Domain\Auth\Services\Auth::class)->rememberIntendedUrl(
            rtrim(BASE_URL, '/').'/oauth/authorize'.($queryString ? '?'.$queryString : '')
        );
    }

    /**
     * Whether $request targets an endpoint that must be addressed by its canonical path: any API
     * path (except static assets), cron, or MCP.
     */
    protected function mustUseCanonicalPath(IncomingRequest $request): bool
    {
        if ($request->isApiOrCronRequest() || $request->isMcpRequest()) {
            return true;
        }

        if (! $request->isApiRequest()) {
            return false;
        }

        return ($request->normalizedSegments()[1] ?? '') !== 'static-asset';
    }

    /**
     * Whether the request carries an explicit API credential (API key or Bearer token).
     */
    protected function presentedApiCredential(IncomingRequest $request): bool
    {
        if ($request->headers->has('x-api-key')) {
            return true;
        }

        $bearer = method_exists($request, 'getBearerToken') ? $request->getBearerToken() : $request->bearerToken();

        return ! empty($bearer);
    }

    /**
     * Whether the current session satisfies two-factor authentication: either 2FA is not enabled
     * for the user, or the code has been verified in this session. Mirrors the web check in
     * {@see self::authenticateWeb()}.
     */
    protected function sessionTwoFASatisfied(): bool
    {
        return ! session('userdata.twoFAEnabled') || (bool) session('userdata.twoFAVerified');
    }

    /**
     * Whether this client IP has exceeded the failed-authentication budget (shared with the
     * login attempt limit, LEAN_RATELIMIT_AUTH). Counted per minute.
     */
    protected function tooManyFailedAuthAttempts(IncomingRequest $request): bool
    {
        $limit = $this->config->ratelimitAuth ?? 20;

        return app(RateLimiter::class)->tooManyAttempts($this->failedAuthKey($request), $limit);
    }

    /**
     * Record a failed authentication attempt for this client IP (1-minute decay).
     */
    protected function hitFailedAuthLimiter(IncomingRequest $request): void
    {
        app(RateLimiter::class)->hit($this->failedAuthKey($request), 60);
    }

    /**
     * Rate limiter key for failed token-auth attempts, scoped to the client IP.
     */
    protected function failedAuthKey(IncomingRequest $request): string
    {
        return 'api-auth-failures:'.$request->getClientIp();
    }

    /**
     * Establish the Leantime user context (`session('userdata')`) for an authenticated API request.
     *
     * Every API guard must leave the SAME context behind: the permission engine — and everything
     * else — reads the user's id and role from `session('userdata')`. The x-api-key guard populates
     * it as a side effect of {@see \Leantime\Domain\Api\Services\Api::getAPIKeyUser()}, but the
     * Sanctum (Bearer) guard resolves the user straight from its token and never does — so the
     * engine saw no user and denied every gated `@api` method with -32001 on Bearer requests.
     *
     * This makes the HTTP API auth path uniform: whichever guard authenticated, the context is
     * built once, from the canonical user row, through the same `setApiUserSession()` builder the
     * x-api-key path uses. Idempotent — it skips when a guard already populated `userdata`
     * (x-api-key, or a stateful web session), so those paths are byte-for-byte untouched. Services
     * are resolved lazily because this only runs for an authenticated API request.
     */
    protected function establishApiUserSession(IncomingRequest $request): void
    {
        if (session()->exists('userdata') || ($apiUser = $request->user()) === null) {
            return;
        }

        $userData = app(\Leantime\Domain\Users\Services\Users::class)->getUser((int) $apiUser->id);

        if (is_array($userData)) {
            app(\Leantime\Domain\Api\Services\Api::class)->setApiUserSession($userData, true);
        }
    }

    /**
     * Redirect with origin
     * Returns false if the current route is already the redirection route.
     *
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function redirectWithOrigin(string $route, string $origin, IncomingRequest $request): false|RedirectResponse
    {

        $uri = ltrim(str_replace('.', '/', $route), '/');
        $destination = BASE_URL.'/'.$uri;
        $originClean = Str::replaceStart('/', '', $origin);
        $queryParams = ! empty($origin) && $origin !== '/' ? '?'.http_build_query(['redirect' => $originClean]) : '';

        if ($request->getCurrentRoute() === $route) {
            return false;
        }

        return new RedirectResponse($destination.$queryParams);
    }

    /**
     * Whether a route (dot separated, as from getCurrentRoute()) is in the public actions list.
     * Compares "module.action". "/.well-known/…" routes are never public here; see
     * {@see self::isPublicWellKnownPath()}.
     *
     * @param  string|null  $currentPath  The current route, e.g. "auth.login" or ".well-known.caldav"
     */
    public function isPublicController($currentPath): bool
    {

        // path comes in with dots as separator.
        // We only need to compare the first 2 segments
        if (empty($currentPath)) {
            return false;
        }

        // "/.well-known/…" paths are judged by isPublicWellKnownPath() on the real path segments:
        // the dotted route can't tell "/.well-known/security.txt" from "/.well-known/security/txt".
        if (str_starts_with($currentPath, '.well-known.')) {
            return false;
        }

        $pathSegments = explode('.', $currentPath);

        $routeToCheck = match (count($pathSegments)) {
            1 => $pathSegments[0],
            default => $pathSegments[0].'.'.$pathSegments[1],
        };

        return in_array($routeToCheck, $this->publicActions, true);

    }
}
