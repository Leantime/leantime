<?php

/**
 * Module Manager
 */

namespace Leantime\Domain\Modulemanager\Services {

    use Leantime\Domain\Plugins\Services\Plugins;

    class Modulemanager
    {
        use \Leantime\Core\Events\DispatchesEvents;

        private static array $modules = [
            'api' => ['required' => true, 'enabled' => true, 'dependsOn' => '', 'scope' => 'system'],
            'calendar' => ['required' => false, 'enabled' => true, 'dependsOn' => '', 'scope' => 'project'],
            'clients' => ['required' => true, 'enabled' => true, 'dependsOn' => '', 'scope' => 'system'],
            'comments' => ['required' => false, 'enabled' => true, 'dependsOn' => '', 'scope' => 'project'],
            'dashboard' => ['required' => true, 'enabled' => true, 'dependsOn' => '', 'scope' => 'project'],
            'files' => ['required' => false, 'enabled' => true, 'dependsOn' => '', 'scope' => 'project'],
            'general' => ['required' => true, 'enabled' => true, 'dependsOn' => '', 'scope' => 'system'],
            'help' => ['required' => false, 'enabled' => true, 'dependsOn' => '', 'scope' => 'project'],
            'ideas' => ['required' => false, 'enabled' => true, 'dependsOn' => '', 'scope' => 'project'],
            'ldap' => ['required' => false, 'enabled' => true, 'dependsOn' => '', 'scope' => 'system'],
            'leancanvas' => ['required' => false, 'enabled' => true, 'dependsOn' => '', 'scope' => 'project'],
            'projects' => ['required' => true, 'enabled' => true, 'dependsOn' => '', 'scope' => 'system'],
            'read' => ['required' => true, 'enabled' => true, 'dependsOn' => '', 'scope' => 'system'],
            'reports' => ['required' => false, 'enabled' => true, 'dependsOn' => '', 'scope' => 'project'],
            'retroscanvas' => ['required' => false, 'enabled' => true, 'dependsOn' => '', 'scope' => 'project'],
            'setting' => ['required' => true, 'enabled' => true, 'dependsOn' => '', 'scope' => 'system'],
            'sprints' => ['required' => false, 'enabled' => true, 'dependsOn' => 'tickets', 'scope' => 'project'],
            'tickets' => ['required' => true, 'enabled' => true, 'dependsOn' => '', 'scope' => 'project'],
            'timesheets' => ['required' => false, 'enabled' => true, 'dependsOn' => '', 'scope' => 'project'],
            'twoFA' => ['required' => false, 'enabled' => true, 'dependsOn' => '', 'scope' => 'system'],
            'users' => ['required' => true, 'enabled' => true, 'dependsOn' => '', 'scope' => 'system'],
            'modulemanager' => ['required' => true, 'enabled' => true, 'dependsOn' => '', 'scope' => 'system'],
        ];

        private Plugins $pluginService;

        /**
         * __construct - get and test Session or make session
         */
        public function __construct(Plugins $plugins)
        {
            $this->pluginService = $plugins;
        }

        public static function isModuleEnabled($module): bool
        {
            if (isset(self::$modules[$module])) {
                if (self::$modules[$module]['enabled'] === true) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Checks if a module is available.
         * In Progress: This method is a stub to hook into via filters.
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

            $available = static::dispatchFilter('moduleAvailability', $available, ['module' => $module]);

            return $available;
        }
    }

}
