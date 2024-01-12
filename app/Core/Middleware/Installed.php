<?php

namespace Leantime\Core\Middleware;

use Closure;
use Leantime\Core\Eventhelpers;
use Leantime\Core\Frontcontroller;
use Leantime\Core\IncomingRequest;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Symfony\Component\HttpFoundation\Response;

class Installed
{
    use Eventhelpers;

    /**
     * Check if Leantime is installed
     *
     * @param \Closure(IncomingRequest): Response $next
     * @throws BindingResolutionException
     **/
    public function handle(IncomingRequest $request, Closure $next): Response
    {
        $session_says = isset($_SESSION['isInstalled']) && $_SESSION['isInstalled'];
        $config_says = app()->make(SettingRepository::class)->checkIfInstalled();

        if (! $session_says && ! $config_says) {
            $this->setUninstalled();

            if (! $response = $this->redirectToInstall()) {
                return $next($request);
            }

            return $response;
        }

        if ($session_says && ! $config_says) {
            $this->setUninstalled();

            if (! $response = $this->redirectToInstall()) {
                return $next($request);
            }

            return $response;
        }

        if (! $session_says && $config_says) {
            $this->setInstalled();
        }

        self::dispatch_event('after_install');

        \Illuminate\Support\Facades\Cache::set('installed', true);

        return $next($request);
    }

    /**
     * Set installed
     *
     * @return void
     */
    private function setInstalled(): void
    {
        $_SESSION['isInstalled'] = true;
    }

    /**
     * Set uninstalled
     *
     * @return void
     */
    private function setUninstalled(): void
    {
        $_SESSION['isInstalled'] = false;

        if (isset($_SESSION['userdata'])) {
            unset($_SESSION['userdata']);
        }
    }

    /**
     * Redirect to install
     *
     * @return Response|false
     * @throws BindingResolutionException
     */
    private function redirectToInstall(): Response|false
    {
        $frontController = app()->make(Frontcontroller::class);

        $allowedRoutes = ['install', 'install.update', 'api.i18n'];
        $allowedRoutes = self::dispatch_filter("allowedRoutes", $allowedRoutes);
        if (in_array($frontController::getCurrentRoute(), $allowedRoutes)) {
            return false;
        }

        $route = BASE_URL . "/install";
        $route = self::dispatch_filter("redirectroute", $route);
        return $frontController::redirect($route);
    }
}
