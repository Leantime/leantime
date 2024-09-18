<?php

namespace Leantime\Core\Bootstrap;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Leantime\Core\Configuration\DefaultConfig;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Providers\Cache;
use Leantime\Core\Support\Attributes\LaravelConfig;

class LoadConfig extends LoadConfiguration
{
    protected $ignoreFiles = [
        'configuration.sample.php',
        'configuration.php',
    ];

    /**
     * Bootstrap the application.
     *
     * This method initializes the application by loading the configuration files and
     * setting up the environment.
     *
     * @param  Application  $app  The application instance.
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $items = [];

        // First we will see if we have a cache configuration file. If we do, we'll load
        // the configuration items from that file so that it is very quick. Otherwise
        // we will need to spin through every configuration file and load them all.
        if (file_exists($cached = $app->getCachedConfigPath())) {
            $items = require $cached;

            $loadedFromCache = true;
        }

        // Next we will spin through all of the configuration files in the configuration
        // directory and load each one into the repository. This will make all of the
        // options available to the developer for use in various parts of this app.
        $app->instance('config', $config = new Environment($items));

        if (! isset($loadedFromCache)) {
            $this->loadConfigurationFiles($app, $config);

            //Now extend config with laravel configs if they exist
            $app->extend('config', function (Repository $laravelConfig) use ($app) {

                $leantimeConfig = $app->make(Environment::class);

                //Add all laravel configs to leantime config
                foreach ($laravelConfig->all() as $key => $value) {
                    $leantimeConfig->set($key, $value);
                }

                //At this point we have the leantime config and loaded laravel configs
                //Re-aranging and setting some of the laravel defaults that were not set
                //as part of the file loader. Laravel config vars were already added.
                $finalConfig = $this->mapLeantime2LaravelConfig($laravelConfig, $leantimeConfig);

                //Additional adjustments
                $finalConfig->set('APP_DEBUG', $finalConfig->get('debug') ? true : false);

                if ($finalConfig->get('app.url') == '') {
                    $url = defined('BASE_URL') ? BASE_URL : 'http://localhost';
                    $finalConfig->set('app.url', $url);
                }

                return $finalConfig;
            });
        }

        $config = $app['config'];

        // Finally, we will set the application's environment based on the configuration
        // values that were loaded. We will pass a callback which will be used to get
        // the environment in a web context where an "--env" switch is not present.
        $app->detectEnvironment(fn () => $config->get('app.env', 'production'));

        date_default_timezone_set($config->get('app.timezone', 'UTC'));

        mb_internal_encoding('UTF-8');

    }

    /**
     * Load the configuration files.
     *
     * This method loads the Laravel configuration files and sets them into the given repository.
     *
     * @param  Application  $app  The application instance.
     * @param  RepositoryContract  $repository  The repository where the configuration files will be set.
     * @return void
     */
    protected function loadConfigurationFiles(Application $app, RepositoryContract $repository)
    {
        $laravelConfig = require APP_ROOT.'/app/Core/Configuration/laravelConfig.php';
        foreach ($laravelConfig as $key => $configArea) {
            $repository->set($key, $configArea);
        }
    }

    /**
     * Maps Leantime configuration options to Laravel configuration options.
     *
     * @param  LaravelConfig  $laravelConfig  The Laravel configuration object to map to.
     * @param  LeantimeConfig  $leantimeConfig  The Leantime configuration object to map from.
     * @return LeantimeConfig The updated Leantime configuration object with mapped values.
     */
    protected function mapLeantime2LaravelConfig($laravelConfig, $leantimeConfig)
    {

        $reflectionClass = new \ReflectionClass(DefaultConfig::class);
        $properties = $reflectionClass->getProperties();

        //Parsing through all the leantime config options.
        //Default tracks a mapping via attributes
        foreach ($properties as $configVar) {
            $attributes = $configVar->getAttributes(LaravelConfig::class);

            if (isset($attributes[0])) {

                $laravelConfigKey = $attributes[0]->newInstance()->config;
                $defaultConfigkey = $configVar->name;

                //set laravel config.
                //Leantime env file has priority and can override previously defined laravel configs
                $leantimeConfig->set($laravelConfigKey, $leantimeConfig->get($defaultConfigkey));
            }
        }

        return $leantimeConfig;
    }

    public function configValidation() {}
}
