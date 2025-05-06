<?php

namespace Leantime\Core\Http;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Facade;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Routing\Frontcontroller;
use Leantime\Infrastructure\Auth\Middleware\AuthenticateSession;

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

        // \Illuminate\Session\Middleware\StartSession::class,
        // \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        // \Illuminate\Auth\Middleware\Authenticate::class,
        // \Illuminate\Session\Middleware\AuthenticateSession::class,
        // \Illuminate\Routing\Middleware\SubstituteBindings::class,
        // \Illuminate\Auth\Middleware\Authorize::class,
        // \Illuminate\Http\Middleware\TrustHosts::class,

        // \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        // \Illuminate\Cookie\Middleware\EncryptCookies::class,
        // \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,

        Middleware\TrustProxies::class,

        \Leantime\Core\Sessions\Middleware\StartSession::class,
        \Leantime\Infrastructure\Installation\Middleware\Installed::class,
        \Leantime\Infrastructure\Installation\Middleware\Updated::class,

        // All enabled plugins will be available from here on out
        \Leantime\Core\Plugins\Middleware\LoadPlugins::class,

        Middleware\InitialHeaders::class,
        // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,

        \Leantime\Infrastructure\Auth\Middleware\AuthCheck::class,
        \Leantime\Infrastructure\Auth\Middleware\AuthenticateSession::class,

        Middleware\RequestRateLimiter::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        Middleware\SetCacheHeaders::class,
        \Leantime\Infrastructure\i18n\Middleware\Localization::class,

        \Leantime\Domain\Projects\Middleware\CurrentProject::class,

    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
        ],
        'api' => [
        ],
        'hx' => [
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
        'auth' => \Leantime\Infrastructure\Auth\Middleware\AuthCheck::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        // 'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        // 'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];

    public function handle($request)
    {
        $this->requestStartedAt = Carbon::now();

        try {
            $response = $this->sendRequestThroughRouter($request);
        } catch (\Throwable $e) {
            $this->reportException($e);

            $response = $this->renderException($request, $e);
        }

        $this->app['events']->dispatch(new RequestHandled($request, $response));

        $response = self::dispatch_filter('beforeSendResponse', $response);

        return $response;
    }

    protected function sendRequestThroughRouter($request)
    {
        $this->app->instance('request', $request);

        Facade::clearResolvedInstance('request');

        $this->bootstrap();

        // Events are discovered and available as part of bootstrapping the providers.
        // Can savely assume events are available here.
        self::dispatch_event('request_started', ['request' => $request]);

        //        if ($request instanceof ApiRequest) {
        //
        //            array_splice($this->middleware, 6, 0, $this->middlewareGroups['api']);
        //
        //        } else {
        //            array_splice($this->middleware, 6, 0, $this->middlewareGroups['web']);
        //        }

        // This filter only works for system plugins
        // Regular plugins are not available until after install verification
        $this->middleware = self::dispatch_filter('middleware', $this->middleware, ['request' => $request]);

        // Main Pipeline
        $response = (new \Illuminate\Routing\Pipeline($this->app))
            ->send($request)
            ->through($this->middleware)
            ->then(fn ($request) =>
                // Then run through plugin pipeline
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

    public function terminate($request, $response)
    {

        self::dispatchEvent('request_terminated', [$request, $response]);

        parent::terminate($request, $response);

    }
}
