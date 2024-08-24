<?php

namespace Leantime\Core\Controller;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\HtmxRequest;
use Leantime\Core\Http\IncomingRequest;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

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
     * @var string - rootpath (is set in index.php)
     */
    private string $rootPath = '';

    /**
     * @var string - last action that was fired
     */
    private static string $lastAction = '';

    /**
     * @var string - fully parsed action
     */
    private static string $fullAction = '';

    /**
     * @var IncomingRequest
     */
    private static IncomingRequest $incomingRequest;

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
    public function __construct(IncomingRequest $incomingRequest)
    {
        $this->rootPath = ROOT;
        self::$incomingRequest = $incomingRequest;
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
    public static function dispatch(string $action = '', int $httpResponseCode = 200): Response
    {
        self::$fullAction = empty($action) ? self::getCurrentRoute() : $action;

        if (self::$fullAction == '') {
            return self::dispatch("errors.error404", 404);
        }

        //execute action
        return self::executeAction(self::$fullAction, array());
    }

    public static function dispatch_request(IncomingRequest $request): Response
    {
        self::$incomingRequest = $request;
        return self::dispatch();
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
    private static function executeAction(string $completeName, array $params = array()): Response
    {
        $namespace = app()->getNamespace(false);
        $actionName = Str::studly(self::getActionName($completeName));
        $moduleName = Str::studly(self::getModuleName($completeName));

        self::dispatch_event("execute_action_start", ["action"=>$actionName, "module"=>$moduleName ]);

        $controllerNs = "Domain";
        $controllerType = self::$incomingRequest instanceof HtmxRequest ? 'Hxcontrollers' : 'Controllers';
        $classname = "$namespace\\$controllerNs\\$moduleName\\$controllerType\\$actionName";

        if (! class_exists($classname)) {
            $classname = "$namespace\\Plugins\\$moduleName\\$controllerType\\$actionName";
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

        //Setting default response code to 200, can be changed in controller
        self::setResponseCode(200);

        self::$lastAction = $completeName;

        self::dispatch_event("execute_action_end", ["action"=>$actionName, "module"=>$moduleName ]);

        return app()->make($classname)->getResponse();
    }

    /**
     * includeAction - possible to include action from everywhere
     *
     * @access public
     * @param string $completeName
     * @param array  $params
     * @return void
     * @throws BindingResolutionException
     */
    public static function includeAction(string $completeName, array $params = array()): void
    {
        self::executeAction($completeName, $params);
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
        $completeName ??= self::getCurrentRoute();
        $actionParts = explode(".", empty($completeName) ? self::getCurrentRoute() : $completeName);

        //If not action name was given, call index controller
        if (is_array($actionParts) && count($actionParts) == 1) {
            return "index";
        } elseif (is_array($actionParts) && count($actionParts) == 2) {
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
        $completeName ??= self::getCurrentRoute();
        $actionParts = explode(".", empty($completeName) ? self::getCurrentRoute() : $completeName);

        if (is_array($actionParts)) {
            return $actionParts[0];
        }

        return "";
    }


    /**
     * getCurrentRoute - gets the current main action in format module.action
     *
     * @access public
     * @return string
     * @throws BindingResolutionException
     */
    public static function getCurrentRoute(): string
    {
        static $route;

        if (isset($route)) {
            return $route;
        }

        $route = app()->make(IncomingRequest::class)->query->get('act', '');

        return $route;
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
     * setResponseCode - sets the response code
     *
     * @param int $responseCode
     * @return void
     */
    public static function setResponseCode(int $responseCode): void
    {
        http_response_code($responseCode);
    }
}
