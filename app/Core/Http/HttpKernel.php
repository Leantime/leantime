<?php

namespace Leantime\Core\Http;

use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Foundation\Bootstrap\LoadConfig;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Facade;
use Leantime\Core\Application;
use Leantime\Core\Bootstrap\BootProviders;
use Leantime\Core\Bootstrap\HandleExceptions;
use Leantime\Core\Bootstrap\LoadEnvironmentVariables;
use Leantime\Core\Bootstrap\RegisterFacades;
use Leantime\Core\Bootstrap\RegisterProviders;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Middleware;

class HttpKernel extends Kernel implements HttpKernelContract
{
    use DispatchesEvents;

    protected $frontcontroller;

    protected $app;

    /**
     * The timestamp when the request started.
     *
     * @var null|int
     */
    protected $requestStartedAt = null;

    public function __construct(Application $app, Frontcontroller $frontcontroller)
    {
        $this->app = $app;
        $this->frontcontroller = $frontcontroller;


    }

    /**
     * Handle the incoming request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The incoming request.
     * @return \Symfony\Component\HttpFoundation\Response  The response.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpResponseException  If an HTTP response exception occurs.
     * @throws \Throwable  If an error occurs and it is not caught.
     *
     * @Overrid
     */
    public function handle($request)
    {

        $this->middleware = $this->getMiddleware($request);
        $this->bootstrappers = $this->getBootstrappers();

        $this->requestStartedAt = \Illuminate\Support\Carbon::now();

        try {
            $request->enableHttpMethodParameterOverride();

            $response = $this->sendRequestThroughRouter($request);
        } catch (Throwable $e) {
            $this->reportException($e);

            $response = $this->renderException($request, $e);
        }

        //Execute event with request handled, in case some laravel listener is listening
        self::dispatch(new RequestHandled($request, $response));

        //filter response
        return self::dispatch_filter('beforeSendResponse', $response);
    }

    /**
     * Send the request through the router.
     *
     * @param mixed $request The request object.
     *
     * @return mixed The response object.
     *
     * @Override
     */
    protected function sendRequestThroughRouter($request)
    {
        $this->app->instance('request', $request);

        Facade::clearResolvedInstance('request');

        $this->bootstrap();

        $response = (new Pipeline($this->app))
            ->send($request)
            ->through($this->app->shouldSkipMiddleware() ? [] : $this->middleware)
            ->then(fn($request) => $this->frontcontroller->dispatch($request));

        return $response;
    }

    /**
     * Terminate the request.
     *
     * @param mixed $request  The request object.
     * @param mixed $response The response object.
     *
     * @return void
     */
    public function terminate($request, $response)
    {

        if (method_exists($this->app, 'terminate')) {
            $this->app->terminate();
        }

        if (is_null($this->requestStartedAt)) {
            return;
        }

        foreach ($this->getMiddleware($request) as $middleware) {
            if (
                ! is_string($middleware)
                || ! class_exists($middleware)
                || ! method_exists($middleware, 'terminate')
            ) {
                continue;
            }

            $this->app->make($middleware)->terminate($request, $response);
        }

        self::dispatch_event('request_terminated', ['request' => $request, 'response' => $response]);

        $this->requestStartedAt = null;
    }



    /**
     * Get the application middleware
     * @return array
     **/
    public function getMiddleware(IncomingRequest $request): array
    {

        $middleware = [
            Middleware\TrustProxies::class,
            Middleware\InitialHeaders::class,
            Middleware\StartSession::class,
            Middleware\Installed::class,
            Middleware\Updated::class,
            Middleware\RequestRateLimiter::class,
        ];

        if ($request instanceof ApiRequest) {
            $middleware[] = Middleware\ApiAuth::class;
        } else {
            $middleware[] = Middleware\Auth::class;
        }

         $middleware[] = Middleware\Localization::class;
         $middleware[] = Middleware\CurrentProject::class;

        return self::dispatch_filter('http_middleware', $middleware);
    }

    public function getBootstrappers(): array
    {

        $bootstrappers = [
            \Leantime\Core\Bootstrap\LoadEnvironmentVariables::class,
            \Leantime\Core\Bootstrap\LoadConfig::class,
            \Leantime\Core\Bootstrap\HandleExceptions::class,
            \Leantime\Core\Bootstrap\RegisterProviders::class,
            \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
            \Illuminate\Foundation\Bootstrap\BootProviders::class
        ];

        return self::dispatch_filter('http_bootstrappers', $bootstrappers);
    }

}
