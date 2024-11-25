<?php

namespace Leantime\Core\Middleware;

use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\ApiRequest;
use Leantime\Core\Http\IncomingRequest;
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
    ];

    public function __construct(
        protected Environment $config
    ) {
        $this->publicActions = self::dispatchFilter('publicActions', $this->publicActions, ['bootloader' => $this]);
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

        if ($request->getCurrentRoute() == $route) {
            return false;
        }

        return new RedirectResponse($destination.$queryParams);
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

        if ( $request instanceof ApiRequest) {
            self::dispatchEvent('before_api_request', ['application' => app()], 'leantime.core.middleware.apiAuth.handle');
        }
        $authCheckResponse = $this->authenticate($request, array_keys($this->config->get('auth.guards')), $loginRedirect, $next);

        if ($authCheckResponse !== true) {
            return $authCheckResponse;
        }

        // Check if trying to access twoFA code page, or if trying to access any other action without verifying the code.
        if (session('userdata.twoFAEnabled') && ! session('userdata.twoFAVerified')) {
            return $this->redirectWithOrigin('twoFA.verify', $_GET['redirect'] ?? '', $request) ?: $next($request);
        }

        self::dispatchEvent('logged_in', ['application' => $this]);

        $response = $next($request);

        if ($authCheckResponse === true) {

            //Set cookie to increase session timeout
            $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie(
                'esl', //Extend Session Lifetime
                'true',
                Date::instance(
                    Carbon::now()->addRealMinutes(app('config')['session']['lifetime'])
                ),
                app('config')['session']['path'],
                app('config')['session']['domain'],
                false,
                app('config')['session']['http_only'] ?? true,
                false,
                app('config')['session']['same_site'] ?? null,
                app('config')['session']['partitioned'] ?? false
            ));
        }

        return $response;
    }

    protected function authenticate($request, array $guards, $loginRedirect, $next)
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::shouldUse($guard);

                return true;
            }
        }

        if ($request instanceof APIRequest) {
            return new Response(json_encode(['error' => 'Invalid API Key']), 401);
        }

        return $this->redirectWithOrigin($loginRedirect, $request->getRequestUri(), $request) ?: $next($request);

    }

    public function isPublicController($currentPath)
    {

        //path comes in with dots as separator.
        //We only need to compare the first 2 segments
        $currentPath = explode('.', $currentPath);

        //Todo: We may want to take out hx if we have public htmx paths
        if (! is_array($currentPath)) {
            return false;
        }

        if (count($currentPath) == 1) {
            if (in_array($currentPath[0], $this->publicActions)) {
                return true;
            }

            return false;
        }

        $controllerPath = $currentPath[0].'.'.$currentPath[1];
        if (in_array($controllerPath, $this->publicActions)) {
            return true;
        }

        return false;

    }
}
