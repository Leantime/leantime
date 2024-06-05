<?php

namespace Leantime\Core\Middleware;

use Closure;
use Leantime\Core\AppSettings;
use Leantime\Core\Eventhelpers;
use Leantime\Core\Frontcontroller;
use Leantime\Core\IncomingRequest;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Symfony\Component\HttpFoundation\Response;

class Updated
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
        $dbVersion = session("dbVersion") ?? app()->make(SettingRepository::class)->getSetting('db-version');
        $settingsDbVersion = app()->make(AppSettings::class)->dbVersion;

        if ($dbVersion !== false) {
            //Setting dbVersion only if there is one in the db
            //Otherwise leave dbVersion unset so we can recheck every time the settings db returns false.
            session(["dbVersion" => $dbVersion]);
        }

        session(["isUpdated" => $dbVersion == $settingsDbVersion]);

        if (session("isUpdated")) {
            return $next($request);
        }

        if (! $response = $this->redirectToUpdate()) {
            return $next($request);
        }

        return $response;
    }

    /**
     * Redirect to update
     *
     * @return Response|false
     * @throws BindingResolutionException
     */
    private function redirectToUpdate(): Response|false
    {
        $frontController = app()->make(Frontcontroller::class);

        $allowedRoutes = ['install', 'install.update', 'api.i18n'];
        $allowedRoutes = self::dispatch_filter("allowedRoutes", $allowedRoutes);
        if (in_array($frontController::getCurrentRoute(), $allowedRoutes)) {
            return false;
        }

        $route = BASE_URL . "/install/update";
        $route = self::dispatch_filter("redirectroute", $route);
        return $frontController::redirect($route);
    }
}
