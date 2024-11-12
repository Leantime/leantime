<?php

namespace Leantime\Core\Providers;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Session\SessionManager;
use Illuminate\Session\SymfonySessionDecorator;
use Illuminate\Support\ServiceProvider;

class Session extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton('session', function ($app) {

            if (! empty($app['config']['useRedis']) && (bool) $app['config']['useRedis'] === true) {

                $app['config']->set('session.driver', 'redis');
                $app['config']->set('session.connection', 'sessions');

            } else {

                $sessionDir = storage_path('framework/sessions/'.get_domain_key());

                //domain key was created as file. Let's remove that
                if (file_exists($sessionDir) && ! is_dir($sessionDir)) {
                    unlink($sessionDir);
                }

                if (! is_dir($sessionDir) && ! mkdir($sessionDir) && ! is_dir($sessionDir)) {
                    throw new \RuntimeException(sprintf('Could not create session directory %s', $sessionDir));
                }

                $app['config']->set('session.files', $sessionDir);
            }

            //Most of this is set in the config but some things aren't clear until we get here.
            $app['config']->set('domain', is_array(parse_url(BASE_URL)) ? parse_url(BASE_URL)['host'] : null);

            $sessionManager = new \Illuminate\Session\SessionManager($app);

            return $sessionManager;
        });



        $this->app->singleton('session.store', function ($app) {
            // First, we will create the session manager which is responsible for the
            // creation of the various session drivers when they are needed by the
            // application instance, and will resolve them on a lazy load basis.
            return $app->make('session')->driver();
        });

        $this->app->singleton(StartSession::class, function ($app) {

            return new StartSession($app->make(SessionManager::class), function () use ($app) {
                return $app->make(CacheFactory::class);
            });
        });

        $this->app->singleton(SymfonySessionDecorator::class, SymfonySessionDecorator::class);
    }
}
