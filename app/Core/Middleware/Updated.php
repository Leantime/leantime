<?php

namespace Leantime\Core\Middleware;

use Closure;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Symfony\Component\HttpFoundation\Response;

class Updated
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
        // For HTMX and API requests, trust the session -- DB version can't change mid-session.
        if (session('isUpdated') && ($request->isHtmxRequest() || $request->isApiOrCronRequest())) {
            return $next($request);
        }

        $dbVersion = session('dbVersion') ?? app()->make(SettingRepository::class)->getSetting('db-version');
        $settingsDbVersion = app()->make(AppSettings::class)->dbVersion;

        if ($dbVersion !== false) {
            // Setting dbVersion only if there is one in the db
            // Otherwise leave dbVersion unset so we can recheck every time the settings db returns false.
            session(['dbVersion' => $dbVersion]);
        }

        $dbVersionInt = $this->getVersionInt($dbVersion);
        $settingsDbVersionInt = $this->getVersionInt($settingsDbVersion);

        session(['isUpdated' => $dbVersionInt >= $settingsDbVersionInt]);

        if (session('isUpdated')) {
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
     * @throws BindingResolutionException
     */
    private function redirectToUpdate(): Response|false
    {
        $frontController = app()->make(Frontcontroller::class);

        $allowedRoutes = ['install', 'install.update', 'api.i18n'];
        $allowedRoutes = self::dispatchFilter('allowedRoutes', $allowedRoutes);
        if (in_array($frontController::getCurrentRoute(), $allowedRoutes)) {
            return false;
        }

        $route = BASE_URL.'/install/update';
        $route = self::dispatchFilter('redirectroute', $route);

        return $frontController::redirect($route);
    }

    private function getVersionInt($version)
    {
        $versionArray = explode('.', $version);
        if (is_array($versionArray) && count($versionArray) == 3) {
            $major = $versionArray[0];
            $minor = str_pad($versionArray[1], 2, '0', STR_PAD_LEFT);
            $patch = str_pad($versionArray[2], 2, '0', STR_PAD_LEFT);
            $newDBVersion = $major.$minor.$patch;

            return $newDBVersion;
        }

        return false;
    }
}
