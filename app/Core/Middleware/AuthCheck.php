<?php

namespace Leantime\Core\Middleware;

use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Str;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\ApiRequest;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Auth\Models\Roles;
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

        if ($this->isPublicController($request->getCurrentRoute())) {
            return $next($request);
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

        // Commenter (client) role may only access client portal, auth, and shared utility routes.
        if (session('userdata.role') === Roles::$commenter && ! $request->isApiOrCronRequest()) {
            $route = $request->getCurrentRoute() ?? '';
            $allowed = self::dispatchFilter('commenterAllowedRoutes', [
                'clientportal',
                'hx.clientportal',
                'auth',
                'errors',
                'api.i18n',
                'api.static-asset',
                'notifications',
                'hx.notifications',
                'files',
                'hx.files',
            ]);

            $isAllowed = false;
            foreach ($allowed as $prefix) {
                if ($route === $prefix || str_starts_with($route, $prefix . '.')) {
                    $isAllowed = true;
                    break;
                }
            }

            if (! $isAllowed) {
                // For HTMX partial requests, trigger a client-side full-page redirect
                // instead of a server-side redirect (which HTMX would inject into the swap target).
                if ($request->headers->get('HX-Request') === 'true') {
                    return new Response('', 204, ['HX-Redirect' => BASE_URL . '/clientportal/showDashboard']);
                }

                return new RedirectResponse(BASE_URL . '/clientportal/showDashboard');
            }
        }

        return $next($request);
    }

    protected function authenticate($request, array $guards, $loginRedirect, $next)
    {
        if ($request->isApiOrCronRequest()) {
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
                if (session('userdata.twoFAEnabled') && ! session('userdata.twoFAVerified')) {
                    $response = $this->redirectWithOrigin('twoFA.verify', $_GET['redirect'] ?? '', $request) ?: $next($request);
                } else {
                    $authenticated = true;
                }

                break;
            }
        }

        if (! $authenticated && ! $response) {
            if ($request instanceof APIRequest) {
                $response = new Response(json_encode(['error' => 'Invalid API Key']), 401);
            } else {
                $response = $this->redirectWithOrigin($loginRedirect, $request->getRequestUri(), $request) ?: $next($request);
            }
        }

        return $authenticated ? true : $response;
    }

    protected function authenticateApi($request, array $guards)
    {
        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                $this->auth->shouldUse($guard);

                return true;
            }
        }

        return new Response(json_encode(['error' => 'Unauthorized']), 401);
    }

    /**
     * Redirect with origin
     * Returns false if the current route is already the redirection route.
     *
     * @return Response|RedirectResponse
     *
     * @throws BindingResolutionException
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

    public function isPublicController($currentPath): bool
    {

        // path comes in with dots as separator.
        // We only need to compare the first 2 segments
        if (empty($currentPath)) {
            return false;
        }

        $pathSegments = explode('.', $currentPath);

        $routeToCheck = match (count($pathSegments)) {
            0 => null,
            1 => $pathSegments[0],
            default => $pathSegments[0].'.'.$pathSegments[1],
        };

        return $routeToCheck !== null && in_array($routeToCheck, $this->publicActions, true);

    }
}
