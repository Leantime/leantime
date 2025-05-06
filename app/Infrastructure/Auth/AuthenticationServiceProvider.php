<?php

namespace Leantime\Infrastructure\Auth;

use Illuminate\Auth\Access\Gate;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;
use Leantime\Domain\Auth\Guards\ApiGuard;
use Leantime\Domain\Auth\Guards\WebGuard;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Auth\Services\AuthUser;
use Leantime\Domain\Oidc\Services\Oidc as OidcService;

class AuthenticationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // $this->app->singleton(AuthService::class, AuthService::class);
        // $this->app->singleton(OidcService::class, OidcService::class);

        $this->registerAuthenticator();
        $this->registerUserResolver();
        $this->registerAccessGate();
        $this->registerRequirePassword();
        $this->registerRequestRebindHandler();
        $this->registerEventRebindHandler();

    }

    protected function registerAuthenticator()
    {
        $this->app->singleton('auth', function ($app) {
            return new \Illuminate\Auth\AuthManager($app);
        });

        $this->app->singleton('auth.driver', fn ($app) => $app['auth']->guard());

    }

    protected function registerUserResolver()
    {
        $this->app->bind(AuthenticatableContract::class, fn ($app) => call_user_func($app['auth']->userResolver()));
    }

    protected function registerAccessGate()
    {
        $this->app->singleton(GateContract::class, function ($app) {
            return new Gate($app, fn () => call_user_func($app['auth']->userResolver()));
        });
    }

    /**
     * Register a resolver for the authenticated user.
     *
     * @return void
     */
    protected function registerRequirePassword()
    {
        $this->app->bind(RequirePassword::class, function ($app) {
            return new RequirePassword(
                $app[ResponseFactory::class],
                $app[UrlGenerator::class],
                $app['config']->get('auth.password_timeout')
            );
        });
    }

    /**
     * Handle the re-binding of the request binding.
     *
     * @return void
     */
    protected function registerRequestRebindHandler()
    {
        $this->app->rebinding('request', function ($app, $request) {
            $request->setUserResolver(function ($guard = null) use ($app) {
                return call_user_func($app['auth']->userResolver(), $guard);
            });
        });
    }

    /**
     * Handle the re-binding of the event dispatcher binding.
     *
     * @return void
     */
    protected function registerEventRebindHandler()
    {
        $this->app->rebinding('events', function ($app, $dispatcher) {
            if (! $app->resolved('auth') ||
                $app['auth']->hasResolvedGuards() === false) {
                return;
            }

            if (method_exists($guard = $app['auth']->guard(), 'setDispatcher')) {
                $guard->setDispatcher($dispatcher);
            }
        });
    }

    public function boot()
    {

        $this->app['auth']->provider('leantimeUsers', function ($app, array $config) {
            return new AuthUser(
                $app->make(\Leantime\Domain\Auth\Services\Auth::class)
            );
        });

        $this->app['auth']->extend('leantime', function ($app, $name, array $config) {
            return new WebGuard(
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
