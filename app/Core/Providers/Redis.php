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

            //Setting up three different redis stores
            //We are using a slightlyt diffewrent config structure and keep redis at the root level of our config

            $cacheConfig = $app['config']['redis']['default'];
            $cacheConfig['prefix'] = 'leantime_cache:';

            $installationConfig = $app['config']['redis']['default'];
            $installationConfig['prefix'] = 'leantime_cache:installation:';

            $sessionsConfig = $app['config']['redis']['default'];
            $sessionsConfig['prefix'] = 'leantime_sessions:';

            //Prepare available redis connections
            //These connections (cache, installation, sessions) can be used for sessions and cache
            if ($app['config']->useCluster) {

                //Cluster configs and prefix management works differently than regular connections
                $options = $app['config']['redis']['options'];

                //The default config is not needed anymore and shouldn't be used since the connection is a cluster
                //connection and won't work in the standard config setup
                $app['config']->set('redis.default', null);
                $app['config']->set('redis.cluster', true);

                $app['config']->set('redis.clusters.cache', array_merge(['options' => $options], [$cacheConfig]));
                $app['config']->set('redis.clusters.cache.options.prefix', $cacheConfig['prefix']);

                $app['config']->set('redis.clusters.installation', array_merge(['options' => $options], [$installationConfig]));
                $app['config']->set('redis.clusters.installation.options.prefix', $installationConfig['prefix']);

                $app['config']->set('redis.clusters.sessions', array_merge(['options' => $options], [$sessionsConfig]));
                $app['config']->set('redis.clusters.sessions.options.prefix', $sessionsConfig['prefix']);

            } else {

                $app['config']->set('redis.cache', $cacheConfig);
                $app['config']->set('redis.installation', $installationConfig);
                $app['config']->set('redis.sessions', $sessionsConfig);

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
