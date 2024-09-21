<?php

/**
 * Module Manager
 *
 */

namespace Leantime\Domain\Modulemanager\Services {


    use Leantime\Domain\Plugins\Services\Plugins;

    /**
     *
     */
    class Modulemanager
    {
        use \Leantime\Core\Events\DispatchesEvents;

        private static array $modules = array(
            "api" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "system"),
            "calendar" => array("required" => false, "enabled" => true, "dependsOn" => "", "scope" => "project"),
            "clients" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "system"),
            "comments" => array("required" => false, "enabled" => true, "dependsOn" => "", "scope" => "project"),
            "dashboard" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "project"),
            "files" => array("required" => false, "enabled" => true, "dependsOn" => "", "scope" => "project"),
            "general" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "system"),
            "help" => array("required" => false, "enabled" => true, "dependsOn" => "", "scope" => "project"),
            "ideas" => array("required" => false, "enabled" => true, "dependsOn" => "", "scope" => "project"),
            "ldap" => array("required" => false, "enabled" => true, "dependsOn" => "", "scope" => "system"),
            "leancanvas" => array("required" => false, "enabled" => true, "dependsOn" => "", "scope" => "project"),
            "projects" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "system"),
            "read" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "system"),
            "reports" => array("required" => false, "enabled" => true, "dependsOn" => "", "scope" => "project"),
            "retroscanvas" => array("required" => false, "enabled" => true, "dependsOn" => "", "scope" => "project"),
            "setting" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "system"),
            "sprints" => array("required" => false, "enabled" => true, "dependsOn" => "tickets", "scope" => "project"),
            "tickets" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "project"),
            "timesheets" => array("required" => false, "enabled" => true, "dependsOn" => "", "scope" => "project"),
            "twoFA" => array("required" => false, "enabled" => true, "dependsOn" => "", "scope" => "system"),
            "users" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "system"),
            "modulemanager" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "system"),
        );


        private Plugins $pluginService;

        /**
         * __construct - get and test Session or make session
         *
         * @access private
         */
        public function __construct(Plugins $plugins)
        {
            $this->pluginService = $plugins;
        }

        /**
         * Checks if a module is available and enabled.
         * This also checks plugins and whether they are installed and enabled
         *
         * @param string $module The name of the module to check availability for.
         *
         * @return bool Returns true if the module is available, false otherwise.
         */
        public function isModuleAvailable(string $module): bool
        {
            $available = false;

            $plugins = $this->pluginService->getEnabledPlugins();

            $filtered = collect($plugins)->filter(function ($plugin) use ($module) {
                return strtolower($plugin->foldername) == strtolower($module);
            });

            if($filtered->count() > 0){
                $available = true;
            }

            $available = static::dispatch_filter("moduleAvailability", $available, ["module" => $module]);

            return $available;
        }
    }

}
