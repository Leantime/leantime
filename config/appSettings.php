<?php

/**
 * appSettings class - System appSettings
 *
 */

namespace leantime\core {


    class appSettings
    {

        public $appVersion = "2.3.20";

        public $dbVersion = "2.1.15";

        /**
         * __construct
         *
         */
        public function __construct()
        {
        }

        /**
         * loadSettings - load all appSettings and set ini
         *
         */
        public function loadSettings(environment $config)
        {

            if ($config->defaultTimezone != '') {
                date_default_timezone_set($config->defaultTimezone);
            } else {
                date_default_timezone_set('America/Los_Angeles');
            }

            if ($config->debug === 1 || $config->debug === true) {
                error_reporting(E_ALL);
                ini_set('display_errors', 1);
            } else {
                error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
                ini_set('display_errors', 0);
            }

            if($config->useRedis !== false) {
                ini_set('session.save_handler', 'redis');
                ini_set('session.save_path', $config->redisURL);
            }

            if (session_status() !== PHP_SESSION_ACTIVE) {
                ini_set('session.use_cookies', 1);
                ini_set('session.use_only_cookies', 1);
                ini_set('session.cookie_httponly', 1);
                ini_set('session.use_trans_sid', 0);
            }

            ini_set("log_errors", 1);

            if($config->logPath != '') {
                ini_set('error_log', $config->logPath);
            }else{
                ini_set('error_log', APP_ROOT."/logs/error.log");
            }

        }

        public function getRequestURI($baseURL = "")
        {

            //$_SERVER['REQUEST_URI'] will include the subfolder if one is set. Let's make sure to take it out
            if ($baseURL != "") {
                $trimmedBaseURL = rtrim($baseURL, "/");
                $baseURLParts = explode("/", $trimmedBaseURL);

                //We only need to update Request URI if we have a subfolder install
                if (is_array($baseURLParts) && count($baseURLParts) == 4) {
                    //0: http, 1: "", 2: domain.com 3: subfolder
                    $subfolderName = $baseURLParts[3];

                    //Remove subfoldername from Request URI
                    $requestURI = preg_replace('/^\/' . $subfolderName . '/', '', $_SERVER['REQUEST_URI']);

                    return $requestURI;
                }
            }

            return $_SERVER['REQUEST_URI'];
        }


    }
}
