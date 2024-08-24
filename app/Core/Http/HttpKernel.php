<?php

namespace Leantime\Core\Http;

use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Pipeline\Pipeline;
use Leantime\Core\Bootstrap\Application;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Events\DispatchesEvents;
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

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * Bootstrap the application if it has not been previously bootstrapped.
     *
     * @return void
     */
    public function bootstrap()
    {
        if ($this->app->hasBeenBootstrapped()) {
            return;
        }

        $this->app->boot();
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
        $this->requestStartedAt = microtime(true);

        //Main Pipeline
        $response = (new Pipeline($this->app))
            ->send($request)
            ->through($this->getMiddleware())
            ->then(fn ($request) =>
                //Then run through plugin pipeline
            (new Pipeline($this->app))
                ->send($request)
                ->through(self::dispatch_filter(
                    hook: 'plugins_middleware',
                    payload: [],
                    function: 'handle',
                ))
                ->then(fn () => Frontcontroller::dispatch_request($request))
            );

        return self::dispatch_filter('beforeSendResponse', $response);

    }

    /**
     * Terminate the request.
     *
     * @param mixed $request The request object.
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
     * @return \Leantime\Core\Bootstrap\Application
     */
    public function getApplication(): \Leantime\Core\Bootstrap\Application
    {
        return $this->app;
    }

    /**
     * Get the application middleware
     * @return array
     **/
    public function getMiddleware(): array
    {
        return self::dispatch_filter('http_middleware', [
            Middleware\TrustProxies::class,
            Middleware\InitialHeaders::class,
            Middleware\StartSession::class,
            Middleware\Installed::class,
            Middleware\Updated::class,
            Middleware\RequestRateLimiter::class,
            $this->app->make(IncomingRequest::class) instanceof ApiRequest
                ? Middleware\ApiAuth::class
                : Middleware\Auth::class,
            Middleware\Localization::class,
            Middleware\CurrentProject::class,
        ]);
    }
}
