<?php

namespace Leantime\Core\Routing;

use Illuminate\Support\Facades\Cache;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\EventDispatcher;

class RouteLoader
{
    /**
     * Load all routes.php files from domains and plugins
     */
    public static function loadRoutes(): void
    {
        static $loaded;
        $loaded ??= false;

        if ($loaded) {
            return;
        }

        // Load domain routes
        self::loadDomainRoutes();

        // Load system plugin routes (defined via config)
        self::loadSystemPluginRoutes();

        // Load user plugin routes (will be called via event after plugins are loaded)
        EventDispatcher::add_event_listener('leantime.core.middleware.loadplugins.handle.pluginsStart', function () {
            self::loadUserPluginRoutes();
        });

        $loaded = true;
    }

    /**
     * Load routes.php files from all domains
     */
    private static function loadDomainRoutes(): void
    {
        if ((bool) config('debug') === false) {
            $domainPaths = Cache::store('installation')->rememberForever('domainRoutes', function () {
                return self::getDomainPaths();
            });
        } else {
            $domainPaths = self::getDomainPaths();
        }

        foreach ($domainPaths as $domainPath) {
            if (file_exists($routesPath = "$domainPath/routes.php")) {
                require_once $routesPath;
            }
        }
    }

    /**
     * Load routes.php files from system plugins (defined in config)
     */
    private static function loadSystemPluginRoutes(): void
    {
        if (
            isset(app(Environment::class)->plugins)
            && $configPlugins = explode(',', app(Environment::class)->plugins)
        ) {
            foreach ($configPlugins as $plugin) {
                if (file_exists($pluginRoutesPath = APP_ROOT.'/app/Plugins/'.$plugin.'/routes.php')) {
                    require_once $pluginRoutesPath;
                }
            }
        }
    }

    /**
     * Load routes.php files from user-enabled plugins
     */
    private static function loadUserPluginRoutes(): void
    {
        if (! session('isInstalled')) {
            return;
        }

        try {
            $pluginService = app()->make(\Leantime\Core\Plugins\Plugins::class);
            $enabledPluginPaths = $pluginService->getEnabledPluginPaths();

            foreach ($enabledPluginPaths as $pluginInfo) {
                $routesPath = $pluginInfo['path'].'/routes.php';

                if (file_exists($routesPath)) {
                    require_once $routesPath;
                }
            }
        } catch (\Exception $e) {
            // Silently continue if plugin service is unavailable
        }
    }

    /**
     * Get all domain paths (same as EventDispatcher::getDomainPaths but public for reuse)
     */
    public static function getDomainPaths(): array
    {
        return collect(glob(APP_ROOT.'/app/Domain'.'/*', GLOB_ONLYDIR))->all();
    }
}
