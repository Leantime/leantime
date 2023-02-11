<?php

/**
 * appSettings class - System appSettings
 *
 */

namespace leantime\core {


    class appSettings
    {

        public $appVersion = "2.3.7";

        public $dbVersion = "2.1.14";

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
        public function loadSettings($timezone, $debug, $logPath)
        {

            if ($timezone != '') {
                date_default_timezone_set($timezone);
            } else {
                date_default_timezone_set('America/Los_Angeles');
            }

            if ($debug === 1 || $debug === true) {
                error_reporting(E_ALL);
                ini_set('display_errors', 1);
            } else {
                error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
                ini_set('display_errors', 0);
            }

            if (session_status() !== PHP_SESSION_ACTIVE) {
                ini_set('session.use_cookies', 1);
                ini_set('session.use_only_cookies', 1);
                ini_set('session.cookie_httponly', 1);
                ini_set('session.use_trans_sid', 0);
            }

            ini_set("log_errors", 1);

            if($logPath != '') {
                ini_set('error_log', $logPath);
            }else{
                ini_set('error_log', APP_ROOT."/logs/error.log");
            }

        }


    }
}
