<?php

namespace Leantime\Core\Middleware;

use Closure;
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

        //Event Registrar hooks into this and calls enabled plugin register files
        self::dispatchEvent('pluginsStart', ['request' => $request]);

        // Good event to use for all kinds of plugin events that should run early on like adding language files
        self::dispatchEvent('pluginsEvents', ['request' => $request], 'leantime.core.middleware.loadplugins.handle');

        $response = $next($request);

        self::dispatchEvent('pluginsTermintate', ['request' => $request, 'response' => $response]);

        return $response;
    }
}
