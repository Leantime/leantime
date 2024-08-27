<?php

namespace Leantime\Core\Http;

use Carbon\Carbon;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Foundation\Bootstrap\LoadConfig;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Facade;
use Leantime\Core\Application;
use Leantime\Core\Bootstrap\BootProviders;
use Leantime\Core\Bootstrap\HandleExceptions;
use Leantime\Core\Bootstrap\LoadEnvironmentVariables;
use Leantime\Core\Bootstrap\RegisterFacades;
use Leantime\Core\Bootstrap\RegisterProviders;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Exceptions\ExceptionHandler;
use Leantime\Core\Middleware;

class HttpKernel implements HttpKernelContract
{
    use DispatchesEvents;

    /**
     * The timestamp when the request started.
     *
     * @var null|int
     */
    protected $requestStartedAt = null;

    protected Application $app;

    public function __construct(Application $app)
    {

        $this->app = $app;
    }

    /**
     * Bootstrap the application if it has not been previously bootstrapped.
     *
     * @return void
     */
    public function bootstrap()
    {
        if (! $this->app->hasBeenBootstrapped()) {
            $this->app->bootstrapWith($this->getBootstrappers());
        }

        //$this->app->boot();
    }

    /**
     * Handle the incoming request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The incoming request.
     * @return \Symfony\Component\HttpFoundation\Response  The response.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpResponseException  If an HTTP response exception occurs.
     * @throws \Throwable  If an error occurs and it is not caught.
     */
    public function handle($request)
    {
        $this->requestStartedAt = Carbon::now();

        $this->app->instance('request', $request);

        Facade::clearResolvedInstance('request');

        $this->bootstrap();

        $request->setUrlConstants($this->app['config']->appUrl);

        try {
            //Main Pipeline
            $response = (new Pipeline($this->app))
                ->send($request)
                ->through($this->getMiddleware())
                ->then(fn($request) => //Then run through plugin pipeline
                    (new Pipeline($this->app))
                        ->send($request)
                        ->through(
                        self::dispatch_filter(
                            hook: 'plugins_middleware',
                            payload: [],
                            function: 'handle',
                        )
                    )
                    ->then(function($request) {
                        return $this->app['frontcontroller']->dispatch();
                    })
                );

        } catch (Throwable $e) {
            $this->reportException($e);

            $response = $this->renderException($request, $e);
        }

        return self::dispatch_filter('beforeSendResponse', $response);
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

        foreach ($this->getMiddleware() as $middleware) {
            if (
                ! is_string($middleware)
                || ! class_exists($middleware)
                || ! method_exists($middleware, 'terminate')
            ) {
                continue;
            }

            $this->app->make($middleware)->terminate($request, $response);
        }

        //report("Before Request Terminated");
        //report(print_r($request, true));
        self::dispatch_event('request_terminated', ['request' => $request, 'response' => $response]);

        $this->requestStartedAt = null;
    }

    /**
     * Get the application instance.
     *
     * @return \Leantime\Core\Application
     */
    public function getApplication(): \Leantime\Core\Application
    {
        return $this->app;
    }

    /**
     * Get the application middleware
     * @return array
     **/
    public function getMiddleware(): array
    {

        $middleware = [
            Middleware\TrustProxies::class,
            Middleware\InitialHeaders::class,
            Middleware\StartSession::class,
            Middleware\Installed::class,
            Middleware\Updated::class,
            Middleware\RequestRateLimiter::class,
        ];

        if ($this->app->make(IncomingRequest::class) instanceof ApiRequest) {
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
            LoadEnvironmentVariables::class,
            \Leantime\Core\Bootstrap\LoadConfig::class,
            HandleExceptions::class,
            RegisterProviders::class,
            RegisterFacades::class,
            BootProviders::class,
        ];

        /*
         * \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
            \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
            \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
            \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
            \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
            \Illuminate\Foundation\Bootstrap\BootProviders::class
         */

        return self::dispatch_filter('http_bootstrappers', $bootstrappers);
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param  \Throwable $e
     * @return void
     */
    protected function reportException(Throwable $e)
    {
        $this->app[ExceptionHandler::class]->report($e);
    }

    /**
     * Render the exception to a response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Throwable               $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderException($request, Throwable $e)
    {
        return $this->app[ExceptionHandler::class]->render($request, $e);
    }
}
