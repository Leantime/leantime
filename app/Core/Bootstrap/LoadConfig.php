<?php

namespace Leantime\Core\Bootstrap;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Leantime\Core\Configuration\DefaultConfig;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Support\Attributes\LaravelConfig;
use Symfony\Component\Finder\Finder;

class LoadConfig extends LoadConfiguration
{

    protected $ignoreFiles = [
        "configuration.sample.php",
        "configuration.php"
    ];

    public function bootstrap(Application $app){

        parent::bootstrap($app);

        $app->extend('config', function(Repository $laravelConfig) use ($app) {

            $leantimeConfig = $app->make(Environment::class);

            foreach ($laravelConfig->all() as $key => $value) {
                $leantimeConfig->set($key, $value);
            }

            //Re-aranging and setting some of the laravel defaults
            $finalConfig = $this->mapLeantime2LaravelConfig($laravelConfig, $leantimeConfig);


            return $finalConfig;
        });


    }

    /**
     * Loads configuration files into the repository.
     *
     * @param Application $app The application instance.
     * @param RepositoryContract $repository The repository contract.
     * @return void
     */
    protected function loadConfigurationFiles(Application $app, RepositoryContract $repository)
    {
        $files = $this->getConfigurationFiles($app);

        //We are allowing laravel configuration files in addition to our main config.
        //However they are optional. Laravel requires app config to be set, so we're
        //pretending it exists.
        //This override removes the check for $file['app']

        foreach ($files as $key => $path) {
            $repository->set($key, require $path);
        }
    }


    /**
     * Get Laravel configs
     *
     * @param  Application  $app
     * @return array
     */
    protected function getConfigurationFiles(Application $app)
    {

        $files = [];

        $configPath = realpath($app->configPath());

        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            $directory = $this->getNestedDirectory($file, $configPath);

            //Ignore leantime configs when loading laravel configs
            if(!in_array($file->getFilename(), $this->ignoreFiles)) {
                $files[$directory.basename($file->getRealPath(), '.php')] = $file->getRealPath();
            }
        }

        ksort($files, SORT_NATURAL);

        return $files;
    }

    protected function mapLeantime2LaravelConfig($laravelConfig, $leantimeConfig) {

        $reflectionClass = new \ReflectionClass(DefaultConfig::class);
        $properties = $reflectionClass->getProperties();

        foreach($properties as $configVar) {
            $attributes = $configVar->getAttributes(LaravelConfig::class);

            if(isset($attributes[0])){
                $laravelConfigKey = $attributes[0]->newInstance()->config;
                $leantimeConfig->set($laravelConfigKey, $laravelConfig->get($configVar->name));
            }
        }

        return $leantimeConfig;
/*
        if(!isset($laravelItems['app'])){
            $laravelItems['app'] = [];
        }*/

        //$laravelItems['app']['name'] = $leantimeItems

    }
}
