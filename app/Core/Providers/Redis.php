<?php

namespace Leantime\Core\Providers;

use Illuminate\Contracts\Redis\Factory;
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
        $this->app->singleton(RedisManager::class, function ($app) {


            app('config')['redis'] = [
                'client' => 'predis',
                'options' => [
                    'cluster' =>  app('config')->redisCluster ?? 'redis',
                    'prefix' =>  app('config')->redisPrefix ?? 'ltRedis',
                ],
                'default' => [
                    'url' =>  app('config')->redisUrl ?? '',
                    'host' =>  app('config')->redisHost ?? '127.0.0.1',
                    'password' =>   app('config')->redisPassword ?? null,
                    'port' =>  app('config')->redisPort ?? '6379',
                    'database' => '0',
                    'prefix' => 'c:'
                ],
                'session' => [
                    'url' =>  app('config')->redisUrl ?? '',
                    'host' =>  app('config')->redisHost ?? '127.0.0.1',
                    'password' =>   app('config')->redisPassword ?? null,
                    'port' =>  app('config')->redisPort ?? '6379',
                    'database' => '0',
                    'prefix' => 's:'
                ],
            ];

            $redisManager = new RedisManager(app(), 'predis', app('config')['redis']);
            return $redisManager;

        });

        $this->app->alias(RedisManager::class, 'redis');
        $this->app->alias(RedisManager::class, Factory::class);
        $this->app->bind('redis.connection', function ($app) {
            return $app['redis']->connection();
        });

    }

    public function boot() {


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
