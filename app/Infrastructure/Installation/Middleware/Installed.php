<?php

namespace Leantime\Infrastructure\Installation\Middleware;

use Closure;
use Leantime\Core\Middleware\BindingResolutionException;
use Leantime\Core\Routing\Frontcontroller;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Symfony\Component\HttpFoundation\Response;

class Installed
{
    use DispatchesEvents;

    /**
     * Check if Leantime is installed
     *
     * @param  \Closure(IncomingRequest): Response  $next
     *
     * @throws BindingResolutionException
     **/
    public function handle(IncomingRequest $request, Closure $next): Response
    {
        $session_says = session()->exists('isInstalled') && session('isInstalled');
        $config_says = app()->make(SettingRepository::class)->checkIfInstalled();

        if (! $session_says && ! $config_says) {
            $this->setUninstalled();

            if (! $response = $this->redirectToInstall($request)) {
                return $next($request);
            }

            return $response;
        }

        if ($session_says && ! $config_says) {
            $this->setUninstalled();

            if (! $response = $this->redirectToInstall($request)) {
                return $next($request);
            }

            return $response;
        }

        if (! $session_says && $config_says) {
            $this->setInstalled();
        }

        self::dispatchEvent('after_install');

        $route = $request->getCurrentRoute();

        if ($session_says && $route == 'install') {
            return Frontcontroller::redirect(BASE_URL.'/auth/logout');
        }

        return $next($request);
    }

    /**
     * Set installed
     */
    private function setInstalled(): void
    {
        session(['isInstalled' => true]);
    }

    /**
     * Set uninstalled
     */
    private function setUninstalled(): void
    {
        session(['isInstalled' => false]);

        if (session()->exists('userdata')) {
            session()->forget('userdata');
        }
    }

    /**
     * Redirect to install
     *
     * @throws BindingResolutionException
     */
    private function redirectToInstall(IncomingRequest $request): Response|false
    {
        $frontController = app()->make(Frontcontroller::class);

        $allowedRoutes = ['install', 'install.update', 'api.i18n'];
        $allowedRoutes = self::dispatchFilter('allowedRoutes', $allowedRoutes);
        $route = $request->getCurrentRoute();
        if (in_array($request->getCurrentRoute(), $allowedRoutes)) {
            return false;
        }

        $route = BASE_URL.'/install';
        $route = self::dispatchFilter('redirectroute', $route);

        return $frontController::redirect($route);
    }
}
