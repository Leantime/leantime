<?php

namespace Leantime\Core;

use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Pipeline\Pipeline;
use Leantime\Core\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use \Symfony\Component\HttpFoundation\RedirectResponse;
use Leantime\Core\Eventhelpers;


class HttpKernel implements HttpKernelContract
{
    use Eventhelpers;

    protected $requestStartedAt = null;

    /**
     * Bootstrap the application for HTTP requests.
     *
     * @return void
     */
    public function bootstrap() {
        if ($this->getApplication()->hasBeenBootstrapped()) {
            return;
        }

        $this->getApplication()->boot();
    }

    /**
     * Handle an incoming HTTP request.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle($request)
    {
        $this->requestStartedAt = microtime(true);

        try {
            return (new Pipeline($this->getApplication()))
                ->send($request)
                ->through($this->getMiddleware())
                ->then(fn () => Frontcontroller::dispatch());
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        } catch (\Throwable $e) {
            if (! app()->make(Environment::class)->debug) {
                return new RedirectResponse(BASE_URL . "/errors/error500", 500);
            }

            throw $e;
        }
    }

    /**
     * Perform any final actions for the request lifecycle.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @return void
     */
    public function terminate($request, $response)
    {
        if (method_exists($this->getApplication(), 'terminate')) {
            $this->getApplication()->terminate();
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

            app()->make($middleware)->terminate($request, $response);
        }

        self::dispatch_event('request_terminated', ['request' => $request, 'response' => $response]);

        $this->requestStartedAt = null;
    }

    /**
     * Get the application instance.
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    public function getApplication()
    {
        return app();
    }

    /**
     * Get the application middleware
     * @return array
     **/
    public function getMiddleware()
    {
        return self::dispatch_filter('http_middleware', [
            Middleware\InitialHeaders::class,
            Middleware\Installed::class,
            Middleware\Updated::class,
            app()->make(IncomingRequest::class) instanceof ApiRequest
                ? Middleware\ApiAuth::class
                : Middleware\Auth::class,
            Middleware\CurrentProject::class,
        ]);
    }
}
