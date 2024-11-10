<?php

namespace Leantime\Core\Providers;

use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Cache\MemcachedConnector;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\Cache\Adapter\Psr16Adapter;

class Cache extends CacheServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton('cache', function ($app) {

            //Now that we know where the instance is bing called from
            //Let's add a domain level cache.
            $domainCacheName = get_domain_key();

            $app['config']->set('cache.stores.'.$domainCacheName, [
                'driver' => 'file',
                'path' => storage_path('framework/cache/'.$domainCacheName.'/data'),
            ]);

            //If redis is set up let's use redis as cache
            if ($app['config']['useRedis']) {

                //Default driver just in case it is being asked for
                $app['config']->set('cache.stores.redis.driver', 'redis');
                $app['config']->set('cache.stores.redis.connection', 'cache');
                $app['config']->set('cache.stores.redis.prefix', 'leantime_cache');

                //Only needed when using sessions with redis
                $app['config']->set('cache.stores.sessions.driver', 'redis');
                $app['config']->set('cache.stores.sessions.connection', 'sessions');
                $app['config']->set('cache.stores.sessions.prefix', 'leantime_sessions');

                $app['config']->set('cache.stores.installation.driver', 'redis');
                $app['config']->set('cache.stores.installation.connection', 'installation');
                $app['config']->set('cache.stores.installation.prefix', 'leantime_cache:installation');

                $app['config']->set('cache.stores.'.$domainCacheName.'.driver', 'redis');
                $app['config']->set('cache.stores.'.$domainCacheName.'.connection', 'cache');
                $app['config']->set('cache.stores.'.$domainCacheName.'.prefix', 'leantime_cache:'.$domainCacheName.'');

            }

            $cacheManager = new \Illuminate\Cache\CacheManager($app);
            $cacheManager->setDefaultDriver($domainCacheName);

            return $cacheManager;
        });

        $this->app->singleton('cache.store', function ($app) {
            return $app['cache']->driver();
        });

        $this->app->singleton('cache.psr6', function ($app) {
            return new Psr16Adapter($app['cache.store']);
        });

        $this->app->singleton('memcached.connector', function () {
            return new MemcachedConnector;
        });

        $this->app->singleton(RateLimiter::class, function ($app) {
            return new RateLimiter($app->make('cache')->driver(
                $app['config']->get('cache.limiter')
            ));
        });

    }

    public function provides()
    {
        return [
            'cache', 'cache.store', 'cache.psr6', RateLimiter::class,
        ];
    }
}
