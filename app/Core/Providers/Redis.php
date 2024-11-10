<?php

namespace Leantime\Core\Providers;

use Illuminate\Redis\RedisManager;
use Illuminate\Support\ServiceProvider;

class Redis extends ServiceProvider
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
        $this->app->singleton('redis', function ($app) {

            $cacheConfig = $app['config']['redis']['default'];
            $cacheConfig['prefix'] = 'leantime_cache';

            $installationConfig = $app['config']['redis']['default'];
            $installationConfig['prefix'] = 'leantime_cache:installation';

            $sessionsConfig = $app['config']['redis']['default'];
            $sessionsConfig['prefix'] = 'leantime_sessions';

            if ($app['config']->useCluster) {

                $app['config']['redis']['clusters'] = [
                    'cache' => [$cacheConfig],
                    'installation' => [$installationConfig],
                    'sessions' => [$sessionsConfig],
                ];

            } else {

                $app['config']['redis'] = [
                    'cache' => $cacheConfig,
                    'installation' => $installationConfig,
                    'sessions' => $sessionsConfig,
                ];

            }

            $redisManager = new RedisManager($app, 'phpredis', $app['config']['redis']);

            return $redisManager;

        });

        $this->app->bind('redis.connection', function ($app) {
            return $app['redis']->connection();
        });

    }

    public function provides()
    {
        return ['redis', 'redis.connection'];
    }

    /**
     * Manages the instance cache.
     */
    public function checkCacheVersion(): void {}
}
