<?php

namespace Leantime\Core\Providers;

use Illuminate\Cache\MemcachedConnector;
use Illuminate\Support\ServiceProvider;
use Leantime\Core\AppSettings;
use Leantime\Core\CliRequest;
use Leantime\Core\Events;
use Leantime\Core\IncomingRequest;
use Leantime\Domain\Setting\Services\Setting as SettingsService;

class Cache extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        /**
         * @todo the following should eventually automatically turn caches into redis if available,
         *  then memcached if available,
         *  then fileStore
         */
        $this->app->singleton(\Illuminate\Cache\CacheManager::class, function ($app) {

            //installation cache is per server
            $this->app['config']['cache.stores.installation'] = [
                'driver' => 'file',
                'connection' => 'default',
                'path' => APP_ROOT . '/cache/installation',
            ];

            //Instance is per company id
            $instanceStore = fn () =>
            $this->app['config']['cache.stores.instance'] = [
                'driver' => 'file',
                'connection' => 'default',
                'path' => APP_ROOT . "/cache/" . $this->app->make(SettingsService::class)->getCompanyId(),
            ];

            if ($this->app->make(IncomingRequest::class) instanceof CliRequest) {
                if (empty($this->app->make(SettingsService::class)->getCompanyId())) {
                    throw new \RuntimeException('You can\'t run this CLI command until you have installed Leantime.');
                }

                $instanceStore();
            } else {
                //Initialize instance cache store only after install was successfull
                Events::add_event_listener(
                    'leantime.core.middleware.installed.handle.after_install',
                    function () use ($instanceStore) {
                        if (! session("isInstalled")) {
                            return;
                        }
                        $instanceStore();
                    }
                );
            }

            $cacheManager = new \Illuminate\Cache\CacheManager($app);

            $cacheManager->setDefaultDriver('instance');

            return $cacheManager;
        });


        $this->app->singleton('cache.store', fn ($app) => $app['cache']->driver());
        $this->app->singleton('cache.psr6', fn ($app) => new \Symfony\Component\Cache\Adapter\Psr16Adapter($app['cache.store']));
        $this->app->singleton('memcached.connector', fn () => new MemcachedConnector());

        $this->app->alias(\Illuminate\Cache\CacheManager::class, 'cache');
        $this->app->alias(\Illuminate\Cache\CacheManager::class, \Illuminate\Contracts\Cache\Factory::class);


    }

    public function boot() {

        $currentVersion = $this->app->make(AppSettings::class)->appVersion;
        $cachedVersion = \Illuminate\Support\Facades\Cache::store('installation')->rememberForever('version', fn () => $currentVersion);

        if ($currentVersion == $cachedVersion) {
            return;
        }

        \Illuminate\Support\Facades\Cache::store('installation')->flush();

    }

    /**
     * Manages the instance cache.
     *
     * @return void
     */
    public function checkCacheVersion(): void
    {


    }

}
