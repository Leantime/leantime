<?php

namespace Leantime\Core;

/**
 * appSettings class - System appSettings
 *
 * @package    leantime
 * @subpackage core
 */
class AppSettings
{
    public $appVersion = "2.4-beta-3";

    public $dbVersion = "2.1.21";

    protected Environment $config;

    /**
     * __construct
     *
     */
    public function __construct(Environment $config)
    {
        $this->config = $config;
    }

    /**
     * loadSettings - load all appSettings and set ini
     *
     */
    public function loadSettings(Environment $config = null)
    {
        $config = $config ?? $this->config;

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

        if ($config->useRedis !== false) {
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

        if ($config->logPath != '' && $config->logPath != 'null') {
            ini_set('error_log', $config->logPath);
        } else {
            ini_set('error_log', APP_ROOT . "/logs/error.log");
        }
    }
}
