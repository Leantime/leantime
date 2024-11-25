<?php

namespace Leantime\Core\Providers;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Support\ServiceProvider;
use Leantime\Domain\Api\Services\Api;
use Leantime\Domain\Auth\Guards\ApiGuard;
use Leantime\Domain\Auth\Guards\LeantimeGuard;
use Leantime\Domain\Auth\Providers\LeantimeUserProvider;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Oidc\Services\Oidc as OidcService;
use Illuminate\Support\Facades\Auth;

class Authentication extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //$this->app->singleton(AuthService::class, AuthService::class);
        //$this->app->singleton(OidcService::class, OidcService::class);
        $this->app->singleton('auth', function ($app) {
            return new \Illuminate\Auth\AuthManager($app);
        });
    }

    public function boot()
    {

        $this->app['auth']->provider('leantimeUsers', function ($app, array $config) {
            return new LeantimeUserProvider(
                $app->make(\Leantime\Domain\Auth\Services\Auth::class)
            );
        });

        $this->app['auth']->extend('leantime', function ($app, $name, array $config) {
            return new LeantimeGuard(
                $app['auth']->createUserProvider($config['provider']),
                $app->make(\Leantime\Domain\Auth\Services\Auth::class)
            );
        });

        $this->app['auth']->extend('jsonRpc', function ($app, $name, array $config) {
            return new ApiGuard(
                        $app['auth']->createUserProvider($config['provider']),
                        $app->make(\Leantime\Domain\Api\Services\Api::class),
                        $app['request']
            );
        });
    }
}
