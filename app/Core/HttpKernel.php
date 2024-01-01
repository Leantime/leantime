<?php

namespace Leantime\Core;

use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Pipeline\Pipeline;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;

class HttpKernel implements HttpKernelContract
{
    use Eventhelpers;

    protected $requestStartedAt = null;

    /**
     * Bootstrap the application for HTTP requests.
     *
     * @return void
     */
    public function bootstrap()
    {
        if ($this->getApplication()->hasBeenBootstrapped()) {
            return;
        }

        $this->getApplication()->boot();
    }

    /**
     * Handle an incoming HTTP request.
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
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
                echo "An error occured";
                error_log($e);
                //return new RedirectResponse(BASE_URL . "/errors/error500", 201);
            }

            if ($request instanceof HtmxRequest) {
                /** @todo Replace with a proper error template for htmx requests */
                return new Response(sprintf(
                    '<dialog style="%s" open>%s</dialog>',
                    'width: 90vw; height: 90vh; z-index: 9999999; position: fixed; top: 5vh; left: 5vh; overflow: scroll',
                    (new HtmlErrorRenderer(true))->render($e)->getAsString(),
                ));
            }

            throw $e;
        }
    }

    /**
     * Perform any final actions for the request lifecycle.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response $response
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
            Middleware\Localization::class,
            Middleware\CurrentProject::class,
        ]);
    }
}
