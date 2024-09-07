<?php

namespace Leantime\Core\Controller;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\HtmxRequest;
use Leantime\Core\Http\IncomingRequest;
use PHPUnit\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Frontcontroller class
 *
 * @package    leantime
 * @subpackage core
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

    /**
     * @var IncomingRequest
     */
    private IncomingRequest $incomingRequest;

    /**
     * @var array - valid status codes
     */
    private array $validStatusCodes = array("100","101","200","201","202","203","204","205","206","300","301","302","303","304","305","306","307","400","401","402","403","404","405","406","407","408","409","410","411","412","413","414","415","416","417","500","501","502","503","504","505");

    /**
     * __construct - Set the rootpath of the server
     *
     * @param IncomingRequest $incomingRequest
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * run - executes the action depending on Request or firstAction
     *
     * @access public
     * @param string $action
     * @param int    $httpResponseCode
     * @return Response
     * @throws BindingResolutionException
     */
    public function dispatch(IncomingRequest $request): Response
    {
        $this->incomingRequest = $request;

        try {

            [$controllerName, $actionName, $method] = $this->parseRequestParts($request);

            //execute action
            return $this->executeAction($this->incomingRequest->getCurrentRoute(), array());

        }catch(Exception $e) {

            return self::redirect(BASE_URL."/error/error404");

        }
    }

    /**
     * parseRequestParts - Parses the request segments and sets the necessary values in the IncomingRequest object.
     *
     * @access public
     * @param IncomingRequest $request The incoming request object.
     * @return array An array containing the controller name, action name, and method.
     */
    public function parseRequestParts(IncomingRequest $request)
    {

        $id = null;

        $segments = $request->segments();
        $method = strtolower($this->incomingRequest->getMethod());

        //First segment is always module
        $controllerName = $segments[0] ?? '';

        //Second is action
        $actionName = $segments[1] ?? '';

        //third is either id or method
        if (isset($segments[2]) && is_numeric($segments[2])) {
            $id = $segments[2];
        }

        if (isset($segments[2]) && !is_numeric($segments[2])) {
            $method = $segments[2];
        }

        $this->incomingRequest->query("u");

        $this->incomingRequest->query->set('act', $controllerName . "." . $actionName . "." . $method);
        $this->incomingRequest->setCurrentRoute($controllerName . "." . $actionName);
        $this->incomingRequest->query->set('id', $id);

        return [$controllerName, $actionName, $method];
    }

    /**
     * executeAction - includes the class in includes/modules by the Request
     *
     * @access private
     * @param string $completeName actionname.filename
     * @param array  $params
     * @return Response
     * @throws BindingResolutionException
     */
    private function executeAction(string $completeName, array $params = array()): Response
    {
        $moduleName = Str::studly(self::getModuleName($completeName));
        $actionName = Str::studly(self::getActionName($completeName));
        $methodName = strtolower(self::getMethodName($completeName));

        $this->dispatch_event("execute_action_start", ["action" => $actionName, "module" => $moduleName]);

        $routeParts = $this->getValidControllerCall($moduleName, $actionName, $methodName);

        //Setting default response code to 200, can be changed in controller
        $this->setResponseCode(200);

        $parameters = $this->incomingRequest->getRequestParams();

        $this->lastAction = $completeName;

        $this->dispatch_event("execute_action_end", ["action" => $actionName, "module" => $moduleName]);

        $controller = app()->make($routeParts["class"]);
        $response = $controller->callAction($routeParts["method"], $parameters);
        return $response instanceof Response ? $response : $controller->getResponse($response);
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
            $this->incomingRequest->header("is-modal") == false &&
            $this->incomingRequest->header("hx-boosted") == false
        ) {
            $controllerType = 'Hxcontrollers';
        }

        return $controllerType;
    }

    /**
     * Retrieves the valid controller call based on the module name, action name, and method name.
     *
     * @param string $moduleName The name of the module.
     * @param string $actionName The name of the action.
     * @param string $methodName The name of the method.
     *
     * @return array The valid controller call in the form of an associative array. The "class" key represents the class path of the controller,
     *     and the "method" key represents the method name of the controller.
     */
    protected function getValidControllerCall(string $moduleName, string $actionName, string $methodName): array
    {

        $controllerType = $this->getControllerType();
        $actionPath = $moduleName . "\\" . $controllerType . "\\" . $actionName;

        if(Cache::has("routes.".$actionPath.".".$methodName)){
            return Cache::get("routes.".$actionPath.".".$methodName);
        }

        $classPath = $this->getClassPath($controllerType, $moduleName, $actionName);
        $classMethod = $this->getValidControllerMethod($classPath, $methodName);

        Cache::set("routes." . $actionPath . "." . ($classMethod == "run" ? $methodName : $classMethod), ["class" => $classPath, "method" => $classMethod]);

        return ["class" => $classPath, "method" => $classMethod];
    }

    /**
     * Retrieves the class path of a controller based on the provided controller type, module name, and action name.
     *
     * @param string $controllerType The type of controller. Possible values are 'Controllers' or 'Hxcontrollers'.
     **/
    public function getClassPath(string $controllerType, string $moduleName, string $actionName): string
    {

        $controllerNs = "Domain";
        $classname = "Leantime\\Domain\\" . $moduleName . "\\" . $controllerType . "\\" . $actionName;

        if (class_exists($classname)) {
            return $classname;
        }

        $classname = "Leantime\\Plugins\\" . $moduleName . "\\" . $controllerType . "\\" . $actionName;

        $enabledPlugins = app()->make(\Leantime\Domain\Plugins\Services\Plugins::class)->getEnabledPlugins();

        $pluginEnabled = false;
        foreach ($enabledPlugins as $key => $obj) {
            if (strtolower($obj->foldername) !== strtolower($moduleName)) {
                continue;
            }
            $pluginEnabled = true;
            break;
        }

        if (! $pluginEnabled || ! class_exists($classname)) {
            throw new RouteNotFoundException("Could not find controller class");
        }

        return $classname;
    }

    /**
     * Retrieves a valid controller method based on the given controller class and method.
     *
     * @param string $controllerClass The fully qualified class name of the controller.
     * @param string $method The method name to check for validity.
     * @return string The valid controller method name. If the given method is "head",
     *     it will be converted to "get". If the given method exists in the controller
     *     class, it will be returned. Otherwise, if the "run" method exists in the
     *     controller class, it will be returned. If no valid method is found, a
     *     RouteNotFoundException will be thrown.
     * @throws RouteNotFoundException If no valid method is found for the given route.
     */
    public function getValidControllerMethod(string $controllerClass, string $method): string
    {

        if (strtoupper($method) == "head") {
            $method = "get";
        }

        if (method_exists($controllerClass, $method)) {
            return $method;
        } elseif (method_exists($controllerClass, 'run')) {
            return 'run';
        }

        throw new RouteNotFoundException("Cannot find a valid method for this route");
    }

    /**
     * getActionName - split string to get actionName
     *
     * @access public
     * @param string|null $completeName
     * @return string
     * @throws BindingResolutionException
     */
    public static function getActionName(string $completeName = null): string
    {
        $completeName ??= currentRoute();
        $actionParts = explode(".", empty($completeName) ? currentRoute() : $completeName);

        //If not action name was given, call index controller
        if (is_array($actionParts) && count($actionParts) == 1) {
            return "index";
        } elseif (is_array($actionParts) && count($actionParts) == 2) {
            return $actionParts[1];
        }

        return "";
    }

    public static function getMethodName(string $completeName = null): string
    {
        $completeName ??= currentRoute();
        $actionParts = explode(".", empty($completeName) ? currentRoute() : $completeName);

        //If not action name was given, call index controller
        if (is_array($actionParts) && count($actionParts) == 2) {
            return strtolower(app('request')->getMethod());
        } elseif (is_array($actionParts) && count($actionParts) == 3) {
            return $actionParts[1];
        }

        return "";
    }

    /**
     * getModuleName - split string to get modulename
     *
     * @access public
     * @param string|null $completeName
     * @return string
     * @throws BindingResolutionException
     */
    public static function getModuleName(string $completeName = null): string
    {
        $completeName ??= currentRoute();
        $actionParts = explode(".", empty($completeName) ? currentRoute() : $completeName);

        if (is_array($actionParts)) {
            return $actionParts[0];
        }

        return "";
    }

    /**
     * redirect - redirects to a given url
     *
     * @param string $url
     * @param int    $http_response_code
     * @return RedirectResponse
     */
    public static function redirect(string $url, int $http_response_code = 303): RedirectResponse
    {
        return new RedirectResponse(
            trim(preg_replace('/\s\s+/', '', strip_tags($url))),
            $http_response_code
        );
    }

    /**
     * getCurrentRoute - gets current route
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
     *
     * @param int $responseCode
     * @return void
     */
    public function setResponseCode(int $responseCode): void
    {
        http_response_code($responseCode);
    }

    protected function getControllerMethod()
    {

        //Get http methods
        $method = $this->incomingRequest;

        //HEAD execution is equal to GET. Server can handle the content response cutting for us.
        if (strtoupper($method) == "HEAD") {
            $method = "GET";
        }

        $available_params = [
            'controller' => $this,
            'method' => $method,
            'params' => $params,
        ];

        self::dispatch_event('before_init', $available_params);
        if (method_exists($this, 'init')) {
            app()->call([$this, 'init']);
        }

        self::dispatch_event('before_action', $available_params);

        if (method_exists($this, $method)) {
            $this->response = $this->$method($params);
        } elseif (method_exists($this, 'run')) {
            $this->response = $this->run();
        } else {
            Log::error('Method not found: ' . $method);
            self::redirect(BASE_URL . "/errors/error501", 307);
        }
    }

    protected function findRoute($request)
    {


        $this->current = $route = $this->routes->match($request);

        //$route->setContainer($this->container);


        $namespace = "Leantime\\";
        $actionName = Str::studly(self::getActionName($completeName));
        $moduleName = Str::studly(self::getModuleName($completeName));

        $this->dispatch_event("execute_action_start", ["action" => $actionName, "module" => $moduleName]);

        $controllerNs = "Domain";

        $controllerType = 'Controllers';
        if (
            ($this->incomingRequest instanceof HtmxRequest) &&
            $this->incomingRequest->header("is-modal") == false &&
            $this->incomingRequest->header("hx-boosted") == false
        ) {
            $controllerType = 'Hxcontrollers';
        }

        $classname = $namespace . "" . $controllerNs . "\\" . $moduleName . "\\" . $controllerType . "\\" . $actionName;

        if (! class_exists($classname)) {
            $classname = $namespace . "Plugins\\" . $moduleName . "\\" . $controllerType . "\\" . $actionName;

            $enabledPlugins = app()->make(\Leantime\Domain\Plugins\Services\Plugins::class)->getEnabledPlugins();

            $pluginEnabled = false;
            foreach ($enabledPlugins as $key => $obj) {
                if (strtolower($obj->foldername) !== strtolower($moduleName)) {
                    continue;
                }
                $pluginEnabled = true;
                break;
            }

            if (! $pluginEnabled || ! class_exists($classname)) {
                return $controllerType == 'Hxcontrollers' ? new Response('', 404) : self::redirect(BASE_URL . "/errors/error404", 307);
            }
        }
    }

}
