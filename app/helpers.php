<?php

use Leantime\Core\Application;
use Leantime\Core\Bootloader;

if (! function_exists('app')) {
    /**
     * Returns the application instance.
     *
     * @param string $abstract
     * @return mixed|\Leantime\Core\Application
     */
    function app(string $abstract = '', array $parameters = []): mixed
    {
        $app = Application::getInstance();
        return !empty($abstract) ? $app->make($abstract, $parameters) : $app;
    }
}

if (! function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param mixed $args
     * @return never
     */
    function dd(...$args): never
    {
        echo sprintf('<pre>%s</pre>', var_export($args, true));
        exit;
    }
}

if (! function_exists('bootstrap_minimal_app')) {
    /**
     * Bootstrap a new IoC container instance.
     *
     * @return \Leantime\Core\Application
     */
    function bootstrap_minimal_app(): Application
    {
        $app = app()->setInstance(new Application())->setHasBeenBootstrapped();
        return Bootloader::getInstance($app)->getApplication();
    }
}

if (! function_exists('__')) {
    /**
     * Translate a string.
     *
     * @param string $index
     * @return string
     */
    function __(string $index): string
    {
        return app()->make(\Leantime\Core\Language::class)->__($index);
    }
}

if (! function_exists('view')) {
    /**
     * Get the view factory instance.
     *
     * @param string $view
     * @param array $data
     * @return \Illuminate\View\Factory
     */
    function view(): \Illuminate\View\Factory
    {
        return app()->make(\Illuminate\View\Factory::class);
    }
}

if (! function_exists('array_sort')) {
    /**
     * sort array of arrqays by value
     *
     * @param array $array
     * @param string $sortyBy
     * @return array
     */
    function array_sort($array, $sortyBy): array
    {
        $collection = collect($array);

        $sorted = $collection->sortBy($sortyBy, SORT_NATURAL);

        return $sorted->values()->all();

    }
}


