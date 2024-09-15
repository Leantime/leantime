<?php

namespace Leantime\Core\Providers;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Session\SessionManager;
use Illuminate\Session\SymfonySessionDecorator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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

            if(! is_dir(storage_path('framework/sessions/'.get_domain_key()))) {
                mkdir(storage_path('framework/sessions/'.get_domain_key()));
            }

            app('config')->set('session.files', storage_path('framework/sessions/'.get_domain_key()));

            if(empty(app('config')['useRedis']) && (bool) app('config')['useRedis'] === true){
                app('config')->set('session.driver', 'redis');
            }

            //Now that we know where the instance is bing called from
            //Let's add a domain level cache.
            $domain = 'localhost';
            if (! $app->runningInConsole()) {
                $domain = $app['request']->getFullUrl();
            }

            //Most of this is set in the config but some things aren't clear until we get here.

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
