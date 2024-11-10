<?php

namespace Leantime\Core\Controller;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\HtmxRequest;
use Leantime\Core\Http\IncomingRequest;
use PHPUnit\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Frontcontroller class
 */
class Frontcontroller
{
    use DispatchesEvents;

    /**
     * @var string - last action that was fired
     */
    private string $lastAction = '';

    /**
     * @var string - fully parsed action
     */
    private string $fullAction = '';

    private IncomingRequest $incomingRequest;

    /**
     * @var array - valid status codes
     */
    private array $validStatusCodes = ['100', '101', '200', '201', '202', '203', '204', '205', '206', '300', '301', '302', '303', '304', '305', '306', '307', '400', '401', '402', '403', '404', '405', '406', '407', '408', '409', '410', '411', '412', '413', '414', '415', '416', '417', '500', '501', '502', '503', '504', '505'];

    protected $defaultRoute = 'dashboard.home';

    protected Environment $config;

    /**
     * __construct - Set the rootpath of the server
     *
     * @param  IncomingRequest  $incomingRequest
     * @return void
     */
    public function __construct(IncomingRequest $request) {
        $this->incomingRequest = $request;
        $this->config = config();
    }

    /**
     * run - executes the action depending on Request or firstAction
     *
     * @param  string  $action
     * @param  int  $httpResponseCode
     *
     * @throws BindingResolutionException
     */
    public function dispatch(IncomingRequest $request): Response
    {
        $this->incomingRequest = $request;

        [$moduleName, $controllerType, $controllerName, $method] = $this->parseRequestParts($request);

        $this->dispatchEvent('execute_action_start', ['action' => $controllerName, 'module' => $moduleName]);

        $routeParts = $this->getValidControllerCall($moduleName, $controllerName, $method, $controllerType);

        //Setting default response code to 200, can be changed in controller
        $this->setResponseCode(200);

        $this->lastAction = $moduleName.'.'.$controllerName.'.'.$method;

        $this->dispatchEvent('execute_action_end', ['action' => $controllerName, 'module' => $moduleName]);

        //execute action
        return $this->executeAction($routeParts['class'], $routeParts['method']);

    }

    public static function dispatch_request(IncomingRequest $request): Response
    {
        $frontcontroller = new self($request);
        return $frontcontroller->dispatch($request);
    }

    /**
     * parseRequestParts - Parses the request segments and sets the necessary values in the IncomingRequest object.
     *
     * @param  IncomingRequest  $request  The incoming request object.
     * @return array An array containing the controller name, action name, and method.
     */
    public function parseRequestParts(IncomingRequest $request)
    {

        $id = null;

        $segments = $request->segments();
        $method = strtolower($this->incomingRequest->getMethod());

        if (count($segments) == 0) {
            $segments = explode('.', $this->defaultRoute);
        }

        //First part is hx tells us this is a htmx controller request
        $controllerType = 'Controllers';
        if ($segments[0] == 'hx') {
            array_shift($segments);
            $controllerType = 'Hxcontrollers';
        }

        //If only one segment part was given the url is mean to be an index placeholder
        if (count($segments) == 1) {
            $segments[] = 'index';
        }

        //First segment is always module
        $moduleName = $segments[0] ?? '';

        //Second is action
        $controllerName = $segments[1] ?? '';

        //third is either id or method
        //we can say that a numeric value always represents an id
        if (isset($segments[2]) &&
            (is_numeric($segments[2]) || Str::isUuid($segments[2]))
        ) {
            $id = $segments[2];
        }

        //If not numeric, it's quite likely this is a method name
        //But it needs to be double checked.
        if (isset($segments[2]) &&
            ! (is_numeric($segments[2]) || Str::isUuid($segments[2]) )
        ) {
            $method = $segments[2];
        }

        $this->incomingRequest->query->set('act', $moduleName.'.'.$controllerName.'.'.$method);
        $this->incomingRequest->setCurrentRoute($moduleName.'.'.$controllerName);

        if(!empty($id)){
            $this->incomingRequest->query->set('id', $id);
        }

        //need to update all controllers to stop using global get and post methods.
        //In the meantime we are setting it again.
        $this->incomingRequest->overrideGlobals();

        return [$moduleName, $controllerType, $controllerName, $method];

    }

    /**
     * executeAction - includes the class in includes/modules by the Request
     *
     * @param  string  $completeName  actionname.filename
     * @param  array  $params
     *
     * @throws BindingResolutionException
     */
    public function executeAction(string $controller, string $method): Response
    {

        $parameters = $this->incomingRequest->getRequestParams();

        $controllerClass = app()->make($controller);

        $response = $controllerClass->callAction($method, $parameters);

        //Expecting a response object but can accept a string to a fragment.
        return $response instanceof Response ? $response : $controllerClass->getResponse($response);
    }


    /**
     * Retrieves the type of controller based on the incoming request.
     *
     * @return string The type of controller. Possible values are 'Controllers' or 'Hxcontrollers'.
     */
    protected function getControllerType(): string
    {

        $controllerType = 'Controllers';
        if (
            ($this->incomingRequest instanceof HtmxRequest) &&
            $this->incomingRequest->header('is-modal') == false &&
            $this->incomingRequest->header('hx-boosted') == false
        ) {
            $controllerType = 'Hxcontrollers';
        }

        return $controllerType;
    }

    /**
     * Retrieves the valid controller call based on the module name, action name, and method name.
     *
     * @param  string  $moduleName  The name of the module.
     * @param  string  $actionName  The name of the action.
     * @param  string  $methodName  The name of the method.
     * @return array The valid controller call in the form of an associative array. The "class" key represents the class path of the controller,
     *               and the "method" key represents the method name of the controller.
     */
    public function getValidControllerCall(string $moduleName, string $actionName, string $methodName, string $controllerType): array
    {

        $moduleName = Str::studly($moduleName);
        $actionName = Str::studly($actionName);
        $methodName = Str::lower($methodName);
        $routepath = $moduleName.'.'.$controllerType.'.'.$actionName;
        $actionPath = $moduleName.'\\'.$controllerType.'\\'.$actionName;

        if($this->config->debug == false) {
            if(Cache::store("installation")->has("routes.".$routepath.".".$methodName)){
                return Cache::store("installation")->get("routes.".$routepath.".".$methodName);
            }
        }

        $classPath = $this->getClassPath($controllerType, $moduleName, $actionName);
        $classMethod = $this->getValidControllerMethod($classPath, $methodName);

        Cache::store('installation')->set('routes.'.$routepath.'.'.($classMethod == 'run' ? $methodName : $classMethod), ['class' => $classPath, 'method' => $classMethod]);

        return ['class' => $classPath, 'method' => $classMethod];
    }

    /**
     * Retrieves the class path of a controller based on the provided controller type, module name, and action name.
     *
     * @param  string  $controllerType  The type of controller. Possible values are 'Controllers' or 'Hxcontrollers'.
     **/
    public function getClassPath(string $controllerType, string $moduleName, string $actionName): string
    {

        $controllerNs = 'Domain';
        $classname = 'Leantime\\Domain\\'.$moduleName.'\\'.$controllerType.'\\'.$actionName;

        if (class_exists($classname)) {
            return $classname;
        }

        //Check if hxcontroller exists
        $classname = 'Leantime\\Domain\\'.$moduleName.'\\Hxcontrollers\\'.$actionName;

        if (class_exists($classname)) {
            return $classname;
        }

        $classname = 'Leantime\\Plugins\\'.$moduleName.'\\'.$controllerType.'\\'.$actionName;

        $enabledPlugins = app()->make(\Leantime\Domain\Plugins\Services\Plugins::class)->getEnabledPlugins();

        $pluginEnabled = false;
        foreach ($enabledPlugins as $key => $obj) {
            if (strtolower($obj->foldername) !== strtolower($moduleName)) {
                continue;
            }
            $pluginEnabled = true;
            break;
        }

        if (! $pluginEnabled) {
           return false;
        }

        if(class_exists($classname)) {
            return $classname;
        }

        $classname = 'Leantime\\Plugins\\'.$moduleName.'\\Hxcontrollers\\'.$actionName;
        if(class_exists($classname)) {
            return $classname;
        }

        return false;
    }

    /**
     * Retrieves a valid controller method based on the given controller class and method.
     *
     * @param  string  $controllerClass  The fully qualified class name of the controller.
     * @param  string  $method  The method name to check for validity.
     * @return string The valid controller method name. If the given method is "head",
     *                it will be converted to "get". If the given method exists in the controller
     *                class, it will be returned. Otherwise, if the "run" method exists in the
     *                controller class, it will be returned. If no valid method is found, a
     *                RouteNotFoundException will be thrown.
     *
     * @throws RouteNotFoundException If no valid method is found for the given route.
     */
    public function getValidControllerMethod(string $controllerClass, string $method): string
    {
        $method = Str::camel($method);
        $httpMethod = Str::lower($this->incomingRequest->getMethod());

        if (Str::lower($method) == 'head') {
            $method = 'get';
        }

        //First check if the given method exists.
        if (method_exists($controllerClass, $method)) {
            return $method;
            //Then check if the http method exists as verb
        } elseif (method_exists($controllerClass, $httpMethod)) {
            //If this was the case our first assumption around $method was wrong and $method is actually a
            //id/slug. Let's set id to that slug.
            $this->incomingRequest->query->set('id', $method);

            return $httpMethod;
            //Just for backwards compatibility, let's also check if run exists.
        } elseif (method_exists($controllerClass, 'run')) {
            return 'run';
        }

        throw new NotFoundHttpException("Can't find valid method for". strip_tags($method) ." in ".strip_tags($controllerClass));
    }

    /**
     * getActionName - split string to get actionName
     *
     * @throws BindingResolutionException
     */
    public static function getActionName(?string $completeName = null): string
    {
        $completeName ??= currentRoute();
        $actionParts = explode('.', empty($completeName) ? currentRoute() : $completeName);

        //If not action name was given, call index controller
        if (is_array($actionParts) && count($actionParts) == 1) {
            return 'index';
        } elseif (is_array($actionParts) && count($actionParts) >= 2) {
            return $actionParts[1];
        }

        return '';
    }

    /**
     * Retrieves the method name based on the complete name of a route.
     *
     * @param  string|null  $completeName  The complete name of the route. Defaults to the current route if not provided.
     * @return string The method name. If the route name consists of two parts (e.g. "controllers.index"), the method name will be the lowercase representation of the current request method
     *. If the route name consists of three parts (e.g. "controllers.update"), the method name will be the second part of the route name. Otherwise, an empty string is returned.
     *
     * @deprecated
     **/
    public static function getMethodName(?string $completeName = null): string
    {
        $completeName ??= currentRoute();
        $actionParts = explode('.', empty($completeName) ? currentRoute() : $completeName);

        //If not action name was given, call index controller
        if (is_array($actionParts) && count($actionParts) == 2) {
            return strtolower(app('request')->getMethod());
        } elseif (is_array($actionParts) && count($actionParts) == 3) {
            return $actionParts[2];
        }

        return '';
    }

    /**
     * getModuleName - split string to get modulename
     *
     * @throws BindingResolutionException
     */
    public static function getModuleName(?string $completeName = null): string
    {
        $completeName ??= currentRoute();
        $actionParts = explode('.', empty($completeName) ? currentRoute() : $completeName);

        if (is_array($actionParts)) {
            return $actionParts[0];
        }

        return '';
    }

    /**
     * redirect - redirects to a given url
     */
    public static function redirect(string $url, int $http_response_code = 303, $headers = []): RedirectResponse
    {

        if (app('request')->headers->get('is-modal')) {
            Frontcontroller::redirectHtmx($url, $headers);
        }

        return new RedirectResponse(
            trim(preg_replace('/\s\s+/', '', strip_tags($url))),
            $http_response_code,
            $headers
        );
    }

    /**
     * redirect - redirects an htmx page.
     *
     * @param  int  $http_response_code
     * @return RedirectResponse
     */
    public static function redirectHtmx(string $url, $headers = []): Response
    {
        //modal redirect
        if (Str::start($url, '#')) {
            $hxCurrentUrl = app('request')->headers->get('hx-current-url');
            $mainPageUrl = Str::before($hxCurrentUrl, '#');
            $url = $mainPageUrl.''.$url;
        }

        $headers['HX-Redirect'] = $url;

        //$headers["hx-push-url"] = $url;
        //$headers["hx-replace-url"] = $url;
        //$headers["HX-Refresh"] = true;

        //this redirect is actually handled on the client side.
        //We'll just return an empty response with a few headers
        return new Response(
            'redirecting...',
            200, //Anything else than 200 will fail.
        );
    }

    /**
     * getCurrentRoute - gets current route
     *
     * @deprecated use request class to get current route
     *
     * @return string
     */
    public static function getCurrentRoute()
    {
        return app('request')->getCurrentRoute();
    }

    /**
     * setResponseCode - sets the response code
     */
    public function setResponseCode(int $responseCode): void
    {
        http_response_code($responseCode);
    }
}
