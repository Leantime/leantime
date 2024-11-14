<?php

namespace Leantime\Core\Middleware;

use Closure;
use Illuminate\Pipeline\Pipeline;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\IncomingRequest;
use Symfony\Component\HttpFoundation\Response;

class LoadPlugins
{
    use DispatchesEvents;

    protected $pluginMiddleware = [];

    /**
     * Set up the initial headers
     *
     * @param  \Closure(IncomingRequest): Response  $next
     *
     * @throws BindingResolutionException
     **/
    public function handle($request, Closure $next): Response
    {

        self::dispatchEvent('pluginsStart', ['request' => $request]);

        $this->pluginMiddleware = self::dispatchFilter('pluginMiddlware', $this->pluginMiddleware, ['request' => $request]);

        $response = app()->make(Pipeline::class)
            ->send($request)
            ->through(app()->shouldSkipMiddleware() ? [] : $this->pluginMiddleware)
            ->then(function ($request) use ($next) {

                //Good event to use for all kinds of plugin events that should run early on like adding language files
                self::dispatchEvent('pluginsEvents', ['request' => $request], 'leantime.core.middleware.loadplugins.handle');

                return $next($request);
            });

        self::dispatchEvent('pluginsTermintate', ['request' => $request, 'response' => $response]);

        return $response;
    }
}
