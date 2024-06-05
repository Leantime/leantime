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

    public string $appVersion = "3.1.4";


    public string $dbVersion = "3.0.2";

    /**
     * __construct
     *
     */
    public function __construct(
        protected Environment $config,
    ) {}

    /**
     * loadSettings - load all appSettings and set ini
     *
     */
    public function loadSettings(Environment $config = null): void
    {
        $config = $config ?? $this->config;

        if ($config->debug === 1 || $config->debug === true) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
            ini_set('display_errors', 0);
        }

        ini_set("log_errors", 1);

        if ($config->logPath != '' && $config->logPath != 'null') {
            ini_set('error_log', $config->logPath);
        } else {
            ini_set('error_log', APP_ROOT . "/logs/error.log");
        }
    }
}
