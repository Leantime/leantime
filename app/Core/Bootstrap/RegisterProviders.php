<?php

namespace Leantime\Core\Bootstrap;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\ServiceProvider;
use Leantime\Core\Application;
use \Leantime\Core\Providers\Db;
use Leantime\Core\Providers\Auth;
use Leantime\Core\Providers\Cache;
use Leantime\Core\Providers\FileSystemServiceProvider;
use Leantime\Core\Providers\Frontcontroller;
use Leantime\Core\Providers\Language;
use Leantime\Core\Providers\RateLimiter;
use Leantime\Core\Providers\Redis;
use Leantime\Core\Providers\Session;
use Leantime\Core\Providers\Views;

class RegisterProviders
{
    /**
     * The service providers that should be merged before registration.
     *
     * @var array
     */
    protected static $merge = [];

    /**
     * The path to the bootstrap provider configuration file.
     *
     * @var string|null
     */
    protected static $bootstrapProviderPath;

    protected static $defaultLeantimeProviders = [
        FileSystemServiceProvider::class,
        Redis::class,
        Cache::class,
        Session::class,
        Auth::class,
        RateLimiter::class,
        \Leantime\Core\Providers\Db::class,
        Language::class,
        Views::class,
        Frontcontroller::class,
    ];


    /**
     * Bootstrap the given application.
     *
     * @param  \Leantime\Core\Application  $app
     * @return void
     */
    public function bootstrap(\Leantime\Core\Application $app)
    {
        if (! $app->bound('config_loaded_from_cache') ||
            $app->make('config_loaded_from_cache') === false) {
            $this->mergeAdditionalProviders($app);
        }

        $app->registerConfiguredProviders();
    }

    /**
     * Merge the additional configured providers into the configuration.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function mergeAdditionalProviders(Application $app)
    {
        if (static::$bootstrapProviderPath &&
            file_exists(static::$bootstrapProviderPath)) {
            $packageProviders = require static::$bootstrapProviderPath;

            foreach ($packageProviders as $index => $provider) {
                if (! class_exists($provider)) {
                    unset($packageProviders[$index]);
                }
            }
        }

        $app->make('config')->set(
            'app.providers',
            array_merge(
                $app->make('config')->get('app.providers') ?? self::$defaultLeantimeProviders,
                static::$merge,
                array_values($packageProviders ?? []),
            ),
        );
    }

    /**
     * Merge the given providers into the provider configuration before registration.
     *
     * @param  array  $providers
     * @param  string|null  $bootstrapProviderPath
     * @return void
     */
    public static function merge(array $providers, ?string $bootstrapProviderPath = null)
    {
        static::$bootstrapProviderPath = $bootstrapProviderPath;

        static::$merge = array_values(array_filter(array_unique(
            array_merge(static::$merge, $providers)
        )));
    }

    /**
     * Flush the bootstrapper's global state.
     *
     * @return void
     */
    public static function flushState()
    {
        static::$bootstrapProviderPath = null;

        static::$merge = [];
    }
}
