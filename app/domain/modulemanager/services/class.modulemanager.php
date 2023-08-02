<?php

/**
 * Module Manager
 *
 */

namespace leantime\domain\services {

    class modulemanager
    {
        private static $modules = array(
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

        /**
         * __construct - get and test Session or make session
         *
         * @access private
         * @return
         */
        public function __construct()
        {
        }

        public static function isModuleEnabled($module)
        {
            if (isset(self::$modules[$module])) {
                if (self::$modules[$module]['enabled'] === true) {
                    return true;
                }
            }

            return false;
        }
    }

}
