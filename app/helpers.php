<?php

use leantime\core\application;
use leantime\core\Bootloader;

if (! function_exists('app')) {
    /**
     * Returns the application instance.
     *
     * @param string $abstract
     * @return \leantime\core\application
     */
    function app(string $abstract = '', array $parameters = []): application
    {
        $app = application::getInstance();
        return !empty($abstract) ? $app->make($abstract, $parameters) : $app;
    }
}

if (! function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param mixed $args
     * @return void
     */
    function dd(...$args): void
    {
        echo sprintf('<pre>%s</pre>', var_export($args, true));
        die(1);
    }
}

if (! function_exists('bootstrap_minimal_app')) {
    /**
     * Bootstrap a new IoC container instance.
     *
     * @return \leantime\core\application
     */
    function bootstrap_minimal_app(): application
    {
        $app = app()->setInstance(new application())->setHasBeenBootstrapped();
        return Bootloader::getInstance($app)->getApplication();
    }
}
