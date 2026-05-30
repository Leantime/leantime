<?php

namespace Leantime\Core\Routing;

use Leantime\Core\Http\IncomingRequest;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bridges a Laravel route to a legacy-style Leantime controller.
 *
 * Leantime controllers are written for the Frontcontroller invocation convention:
 * they read $_GET, expect the request body merged into the $params argument, dispatch
 * by HTTP verb, and may return a string fragment instead of a Response. When a domain
 * needs an explicit Laravel route (e.g. a backward-compat alias whose path the
 * Frontcontroller convention cannot resolve), this helper invokes the target controller
 * exactly the way Frontcontroller::executeAction does.
 *
 * This generalises the per-domain bridge that Blueprints defines inline so the relocated
 * image/upload endpoints (Users, Projects, Files, Setting) can share one implementation.
 */
class ControllerDispatch
{
    /**
     * Invoke a Leantime controller for the current request and return its Response.
     *
     * @param  string  $controllerClass  Fully-qualified controller class name
     */
    public static function dispatch(string $controllerClass): Response
    {
        /** @var IncomingRequest $request */
        $request = app(IncomingRequest::class);
        $route = $request->route();

        // A numeric {id} path segment (canonical routes like /users/profileImage/{id})
        // is how controllers receive the entity id. Mirror Frontcontroller: push it into
        // the query bag and re-sync the PHP superglobals so $_GET['id'] is visible.
        $id = $route?->parameter('id');
        if ($id !== null && $id !== '') {
            $request->query->set('id', $id);
            $request->overrideGlobals();
        }

        $controller = app()->make($controllerClass);

        $verb = strtolower($request->getMethod());
        if ($verb === 'head') {
            $verb = 'get';
        }
        $method = method_exists($controller, $verb) ? $verb : 'get';

        $params = $request->getRequestParams();
        $response = $controller->callAction($method, $params);

        // get()/post() may return a Response directly or a rendered string;
        // wrap the latter via the controller's stored response.
        return $response instanceof Response ? $response : $controller->getResponse();
    }
}
