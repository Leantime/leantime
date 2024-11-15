<?php

namespace Leantime\Core\Http;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Facade;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Events\DispatchesEvents;

class HttpKernel extends Kernel
{
    use DispatchesEvents;

    protected $bootstrappers = [
        \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Leantime\Core\Bootstrap\LoadConfig::class,
        \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
        \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ];

    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \Leantime\Core\Middleware\TrustProxies::class,
        \Leantime\Core\Middleware\SetCacheHeaders::class,
        \Leantime\Core\Middleware\InitialHeaders::class,
        \Leantime\Core\Middleware\StartSession::class,
        \Leantime\Core\Middleware\Installed::class,
        \Leantime\Core\Middleware\Updated::class,
        \Leantime\Core\Middleware\RequestRateLimiter::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Leantime\Core\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \Leantime\Core\Middleware\Auth::class,
        \Leantime\Core\Middleware\ApiAuth::class,
        \Leantime\Core\Middleware\Localization::class,
        \Leantime\Core\Middleware\CurrentProject::class,
        \Leantime\Core\Middleware\LoadPlugins::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \Leantime\Core\Middleware\Auth::class,
            \Leantime\Core\Middleware\Localization::class,
            \Leantime\Core\Middleware\CurrentProject::class,
        ],
        'api' => [
            \Leantime\Core\Middleware\ApiAuth::class,
        ],
        'hx' => [
            \Leantime\Core\Middleware\Auth::class,
            \Leantime\Core\Middleware\Localization::class,
            \Leantime\Core\Middleware\CurrentProject::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * Aliases may be used instead of class names to conveniently assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        //'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        //'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        //'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];

    protected function sendRequestThroughRouter($request)
    {
        $this->app->instance('request', $request);

        Facade::clearResolvedInstance('request');

        $this->bootstrap();

        //Events are discovered and available as part of bootstrapping the providers.
        //Can savely assume events are available here.
        self::dispatch_event('request_started', ['request' => $request]);

        //This filter only works for system plugins
        //Regular plugins are not available until after install verification
        $this->middleware = self::dispatch_filter('middleware', $this->middleware, ['request' => $request]);

        //Main Pipeline
        $response = (new \Illuminate\Routing\Pipeline($this->app))
            ->send($request)
            ->through($this->middleware)
            ->then(fn ($request) =>
                //Then run through plugin pipeline
            (new \Illuminate\Routing\Pipeline($this->app))
                ->send($request)
                ->through(self::dispatch_filter(
                    hook: 'plugins_middleware',
                    payload: [],
                    function: 'handle',
                ))
                ->then(fn () => Frontcontroller::dispatch_request($request))
            );

        return $response;
    }

    public function handle($request)
    {
        $this->requestStartedAt = Carbon::now();

        try {
            $request->enableHttpMethodParameterOverride();

            $response = $this->sendRequestThroughRouter($request);
        } catch (\Throwable $e) {
            $this->reportException($e);

            $response = $this->renderException($request, $e);
        }

        $this->app['events']->dispatch(new RequestHandled($request, $response));

        $response = self::dispatch_filter('beforeSendResponse', $response);

        return $response;
    }

    public function terminate($request, $response)
    {

        self::dispatchEvent('request_terminated', [$request, $response]);

        parent::terminate($request, $response);

    }
}
