<?php

use leantime\core\application;

if (! function_exists('app')) {
    /**
     * Returns the application instance.
     *
     * @param string $abstract
     * @return \leantime\core\application
     */
    function app(string $abstract = '', array $parameters = [])
    {
        if (empty($abstract)) {
            return application::getInstance();
        }

        return application::getInstance()->make($abstract, $parameters);
    }
}

if (! function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param mixed $args
     * @return void
     */
    function dd(...$args)
    {
        echo '<pre>';

        foreach ($args as $x) {
            var_dump($x);
        }

        die(1);
    }
}

if (! function_exists('bootstrap_minimal_app')) {
    /**
     * Create a new IoC container instance.
     *
     * @return \leantime\core\application
     */
    function bootstrap_minimal_app()
    {
        $app = new application();
        $app->setHasBeenBootstrapped();
        $bootloader = Bootloader::getInstance($app);
        $app = Bootloader::getInstance()->getApplication();
        return $app;
    }
}
