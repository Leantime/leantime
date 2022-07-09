<?php

/**
 * Module Manager
 *
 */
namespace leantime\domain\services {

    class modulemanager
    {

        /**
         * @access private
         * @var    static object
         */
        private static $instance = null;


        private static $modules = array(
            "api" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "system"),
            "calendar" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "project"),
            "clients" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "system"),
            "comments" => array("required" => false, "enabled" => true, "dependsOn" => "", "scope" => "project"),
            "dashboard" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "project"),
            "files" => array("required" => false, "enabled" => true, "dependsOn" => "", "scope" => "project"),
            "general" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "system"),
            "help" => array("required" => false, "enabled" => true, "dependsOn" => "", "scope" => "project"),
            "ideas" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "company"),
            "ldap" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "company"),
            "leancanvas" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "company"),
            "projects" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "company"),
            "read" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "company"),
            "reports" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "company"),
            "retrospectives" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "company"),
            "setting" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "company"),
            "sprints" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "company"),
            "tickets" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "company"),
            "timesheets" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "company"),
            "twoFA" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "company"),
            "users" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "company"),
            "modulemanager" => array("required" => true, "enabled" => true, "dependsOn" => "", "scope" => "company"),
        );


        /**
         * __construct - get and test Session or make session
         *
         * @access private
         * @return
         */
        private function __construct()
        {

        }

        /**
         * getInstance - Get instance of session
         *
         * @access private
         * @return object
         */
        public static function getInstance()
        {
            if (self::$instance === null) {
                self::$instance = new self();
            }

            return self::$instance;
        }


    }

}

