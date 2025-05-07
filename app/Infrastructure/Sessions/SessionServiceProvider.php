<?php

namespace Leantime\Infrastructure\Sessions;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Session\SessionManager;
use Illuminate\Session\SessionServiceProvider as LaravelSessionServiceProvider;
use Leantime\Infrastructure\Sessions\Middleware\StartSession;

class SessionServiceProvider extends LaravelSessionServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerSessionManager();

        $this->registerSessionDriver();

        $this->app->singleton(StartSession::class, function ($app) {
            return new StartSession($app->make(SessionManager::class), function () use ($app) {
                return $app->make(CacheFactory::class);
            });
        });

    }

    /**
     * Register the session manager instance.
     *
     * @return void
     *
     * @override
     */
    protected function registerSessionManager()
    {

        $this->app->singleton('session', function ($app) {

            // Switch to redis as session store when setting useRedis is set
            if (! empty($app['config']['useRedis']) && (bool) $app['config']['useRedis'] === true) {

                $app['config']->set('session.driver', 'redis');
                $app['config']->set('session.connection', 'sessions');

            }

            return new \Illuminate\Session\SessionManager($app);
        });
    }
}
