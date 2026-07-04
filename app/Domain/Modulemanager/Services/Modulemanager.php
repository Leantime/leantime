<?php

/**
 * Module Manager
 */

namespace Leantime\Domain\Modulemanager\Services;

use Leantime\Domain\Plugins\Services\Plugins;

class Modulemanager
{
    use \Leantime\Core\Events\DispatchesEvents;

    private Plugins $pluginService;

    /**
     * __construct - get and test Session or make session
     */
    public function __construct(Plugins $plugins)
    {
        $this->pluginService = $plugins;
    }

    /**
     * Checks if a module is available and enabled.
     * This also checks plugins and whether they are installed and enabled
     *
     * @param  string  $module  The name of the module to check availability for.
     * @return bool Returns true if the module is available, false otherwise.
     */
    public function isModuleAvailable(string $module): bool
    {
        $available = false;

        $plugins = $this->pluginService->getEnabledPlugins();

        $filtered = collect($plugins)->filter(function ($plugin) use ($module) {
            return strtolower($plugin->foldername) == strtolower($module);
        });

        if ($filtered->count() > 0) {
            $available = true;
        }

        $available = static::dispatch_filter('moduleAvailability', $available, ['module' => $module]);

        return $available;
    }
}
