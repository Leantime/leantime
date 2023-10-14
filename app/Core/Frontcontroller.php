<?php

namespace Leantime\Core;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use Exception;

/**
 * Frontcontroller class
 *
 * @package    leantime
 * @subpackage core
 */
class Frontcontroller
{
    use Eventhelpers;

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
     * @return void
     * @throws BindingResolutionException
     */
    public static function dispatch(string $action = '', int $httpResponseCode = 200): void
    {
        self::$fullAction = empty($action) ? self::getCurrentRoute() : $action;

        if (self::$fullAction == '') {
            self::dispatch("errors.error404", 404);
        }

        //execute action
        self::executeAction(self::$fullAction, array());
    }

    /**
     * executeAction - includes the class in includes/modules by the Request
     *
     * @access private
     * @param string $completeName actionname.filename
     * @param array  $params
     * @return void
     * @throws BindingResolutionException
     */
    private static function executeAction(string $completeName, array $params = array()): void
    {
        $namespace = app()->getNamespace(false);
        $actionName = Str::studly(self::getActionName($completeName));
        $moduleName = Str::studly(self::getModuleName($completeName));
        $controllerNs = "Domain";
        $controllerType = self::$incomingRequest instanceof HtmxRequest ? 'Hxcontrollers' : 'Controllers';

        //Setting default response code to 200, can be changed in controller
        self::setResponseCode(200);

        $classname = "$namespace\\$controllerNs\\$moduleName\\$controllerType\\$actionName";

        if (! class_exists($classname)) {

            $classname = "$namespace\\Plugins\\$moduleName\\$controllerType\\$actionName";
            $enabledPlugins = app()->make(\Leantime\Domain\Plugins\Services\Plugins::class)->getEnabledPlugins();

            $pluginEnabled = false;
            foreach ($enabledPlugins as $key => $obj) {
                if (strtolower($obj->foldername) == strtolower($moduleName)) {
                    $pluginEnabled = true;
                    if($obj->format == "phar") {
                        include APP_ROOT."/app/Plugins/".$obj->foldername."/".$obj->foldername.".phar";
                    }
                    break;
                }
            }

            if (!$pluginEnabled || !class_exists($classname)) {
                self::redirect(BASE_URL . "/errors/error404", 404);
            }
        }

        app()->make($classname);

        self::$lastAction = $completeName;
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
     * getCurrentRoute - gets the current main action
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
     * @return never
     */
    public static function redirect(string $url, int $http_response_code = 303): never
    {
        header("Location:" . trim(preg_replace('/\s\s+/', '', strip_tags($url))), true, $http_response_code);
        exit();
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
