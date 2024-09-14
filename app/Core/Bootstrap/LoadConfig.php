<?php

namespace Leantime\Core\Bootstrap;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Leantime\Core\Configuration\DefaultConfig;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Providers\Auth;
use Leantime\Core\Providers\Cache;
use Leantime\Core\Providers\ConsoleSupport;
use Leantime\Core\Providers\Db;
use Leantime\Core\Providers\EncryptionServiceProvider;
use Leantime\Core\Providers\FileSystemServiceProvider;
use Leantime\Core\Providers\Frontcontroller;
use Leantime\Core\Providers\Language;
use Leantime\Core\Providers\RateLimiter;
use Leantime\Core\Providers\Redis;
use Leantime\Core\Providers\Session;
use Leantime\Core\Providers\TemplateServiceProvider;
use Leantime\Core\Providers\Views;
use Leantime\Core\Support\Attributes\LaravelConfig;
use Symfony\Component\Finder\Finder;

class LoadConfig extends LoadConfiguration
{
    protected $ignoreFiles = [
        'configuration.sample.php',
        'configuration.php',
    ];

    public function bootstrap(Application $app)
    {
        parent::bootstrap($app);

        //Set a few Leantime config defaults
        $this->setLeantimeDebugConfig($app);

        $this->setLeantimeProviders($app);

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

            return $finalConfig;
        });

    }

    /**
     * Loads configuration files into the repository.
     *
     * @param  Application  $app  The application instance.
     * @param  RepositoryContract  $repository  The repository contract.
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
     * @return array
     */
    protected function getConfigurationFiles(Application $app)
    {

        $files = [];

        $configPath = realpath($app->configPath());

        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            $directory = $this->getNestedDirectory($file, $configPath);

            //Ignore leantime configs when loading laravel configs
            if (! in_array($file->getFilename(), $this->ignoreFiles)) {
                $files[$directory.basename($file->getRealPath(), '.php')] = $file->getRealPath();
            }
        }

        ksort($files, SORT_NATURAL);

        return $files;
    }

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

    protected function setLeantimeDebugConfig(Application $app)
    {

        $app['config']['debug_blacklist'] = [
            '_ENV' => [
                'LEAN_EMAIL_SMTP_PASSWORD',
                'LEAN_DB_PASSWORD',
                'LEAN_SESSION_PASSWORD',
                'LEAN_OIDC_CLIEND_SECRET',
                'LEAN_S3_SECRET',
            ],

            '_SERVER' => [
                'LEAN_EMAIL_SMTP_PASSWORD',
                'LEAN_DB_PASSWORD',
                'LEAN_SESSION_PASSWORD',
                'LEAN_OIDC_CLIEND_SECRET',
                'LEAN_S3_SECRET',
            ],
            '_POST' => [
                'password',
            ],
        ];

    }

    protected function setLeantimeProviders(Application $app)
    {

        $providerList = [ //\Illuminate\Broadcasting\BroadcastServiceProvider::class,
            //\Illuminate\Bus\BusServiceProvider::class,

            Cache::class,
            //\Illuminate\Cache\CacheServiceProvider::class,
            ConsoleSupport::class,
            \Illuminate\Cookie\CookieServiceProvider::class,
            //\Illuminate\Database\DatabaseServiceProvider::class,
            EncryptionServiceProvider::class,
            FileSystemServiceProvider::class,

            \Illuminate\Foundation\Providers\FoundationServiceProvider::class,
            \Illuminate\Hashing\HashServiceProvider::class,
            //\Illuminate\Mail\MailServiceProvider::class,
            \Illuminate\Notifications\NotificationServiceProvider::class,
            \Illuminate\Pagination\PaginationServiceProvider::class,
            //\Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
            \Illuminate\Pipeline\PipelineServiceProvider::class,
            //\Illuminate\Queue\QueueServiceProvider::class,

            Redis::class,
            Session::class,

            //\Illuminate\Redis\RedisServiceProvider::class,
            //\Illuminate\Session\SessionServiceProvider::class,
            //\Illuminate\Translation\TranslationServiceProvider::class,
            \Illuminate\Validation\ValidationServiceProvider::class,
            //\Illuminate\View\ViewServiceProvider::class,

            Auth::class,
            RateLimiter::class,
            Db::class,
            Language::class,
            //RouteServiceProvider::class,

            Frontcontroller::class,
            Views::class,
            TemplateServiceProvider::class,
        ];

        $app['config']->set('app.providers', $providerList);

    }
}
