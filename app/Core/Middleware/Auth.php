<?php

namespace Leantime\Core\Middleware;

use Closure;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Projects\Services\Projects as ProjectsService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class Auth
{
    use DispatchesEvents;

    /**
     * Public actions
     *
     * @var array
     */
    private array $publicActions = array(
        "auth.login",
        "auth.resetPw",
        "auth.userInvite",
        "install",
        "install.update",
        "errors.error404",
        "errors.error500",
        "api.i18n",
        "api.static-asset",
        "calendar.ical",
        "oidc.login",
        "oidc.callback",
        "cron.run",
    );

    public function __construct(
        private Frontcontroller $frontController,
        private AuthService $authService,
        private ProjectsService $projectsService,
    ) {
        $this->publicActions = self::dispatch_filter("publicActions", $this->publicActions, ['bootloader' => $this]);
    }

    /**
     * Redirect with origin
     *
     * @param string $route
     * @param string $origin
     * @return Response|RedirectResponse
     * @throws BindingResolutionException
     */
    public function redirectWithOrigin(string $route, string $origin,): false|RedirectResponse
    {
        $destination = BASE_URL . '/' . ltrim(str_replace('.', '/', $route), '/');
        $queryParams = !empty($origin)  && $origin !== '/' ? '?' . http_build_query(['redirect' => $origin]) : '';

        if ($this->frontController::getCurrentRoute() == $route) {
            return false;
        }

        return new RedirectResponse($destination . $queryParams);
    }

    /**
     * Handle the request
     *
     * @param IncomingRequest $request
     * @param Closure $next
     * @return Response
     */
    public function handle(IncomingRequest $request, Closure $next): Response
    {

        if (in_array($this->frontController::getCurrentRoute(), $this->publicActions)) {
            return $next($request);
        }



        if (! $this->authService->loggedIn()) {
            return $this->redirectWithOrigin('auth.login', $request->getRequestUri()) ?: $next($request);
        }

        // Check if trying to access twoFA code page, or if trying to access any other action without verifying the code.
        if (session("userdata.twoFAEnabled") && ! session("userdata.twoFAVerified")) {
            return $this->redirectWithOrigin('twoFA.verify', $_GET['redirect'] ?? "") ?: $next($request);
        }

        self::dispatch_event("logged_in", ['application' => $this]);

        return $next($request);
    }
}
