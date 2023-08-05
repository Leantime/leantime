<?php

namespace leantime\core;

use Exception;

/**
 * Frontcontroller class
 *
 * @package    leantime
 * @subpackage core
 */
class frontcontroller
{
    use eventhelpers;

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
    private IncomingRequest $incomingRequest;

    /**
     * @var array - valid status codes
     */
    private array $validStatusCodes = array("100","101","200","201","202","203","204","205","206","300","301","302","303","304","305","306","307","400","401","402","403","404","405","406","407","408","409","410","411","412","413","414","415","416","417","500","501","502","503","504","505");

    /**
     * __construct - Set the rootpath of the server
     *
     * @param IncomingRequest $incomingRequest
     * @return self
     */
    public function __construct(IncomingRequest $incomingRequest)
    {
        $this->rootPath = ROOT;
        $this->incomingRequest = $incomingRequest;
    }

    /**
     * run - executes the action depending on Request or firstAction
     *
     * @access public
     * @param  string $action
     * @param  int $httpResponseCode
     * @return void
     */
    public static function dispatch($action = '', $httpResponseCode = 200): void
    {
        //Set action-name from request
        if (isset($_REQUEST['act'])) {
            self::$fullAction = htmlspecialchars($_REQUEST['act']);
        }

        //action parameter overrides Request['act']
        if ($action !== '') {
            self::$fullAction = $action;
        }

        if (self::$fullAction != '') {
            //execute action
            try {
                self::executeAction(self::$fullAction, array(), $httpResponseCode);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        } else {
            self::dispatch("errors.error404", 404);
        }
    }

    /**
     * executeAction - includes the class in includes/modules by the Request
     *
     * @access private
     * @param  string $completeName actionname.filename
     * @param  array $params
     * @return void
     */
    private static function executeAction($completeName, $params = array()): void
    {
        $actionName = self::getActionName($completeName); //actionName is foldername
        $moduleName = self::getModuleName($completeName); //moduleName is filename
        $controllerNs = "domain";

        // initialize plugin service to check
        if ($_SESSION['isInstalled'] === true && $_SESSION['isUpdated'] === true) {
            $pluginService = app()->make(\leantime\domain\services\plugins::class);
        }

        // Check If Route Exists And Fetch Right Route Based On Priority
        $routeExists = false;
        foreach (
            [
            'custom/plugins',
            'custom/domain',
            'plugins',
            'domain',
            ] as $path
        ) {
            $fullpath = ROOT . "/../app/$path/$moduleName/controllers/class.$actionName.php";

            if (!file_exists($fullpath)) {
                continue;
            }

            $routeExists = true;

            if ($path == 'plugins') {
                if (
                    !$_SESSION['isInstalled']
                    || !$_SESSION['isUpdated']
                    || !$pluginService->isPluginEnabled($moduleName)
                ) {
                    self::dispatch("errors.error404", 404);
                    return;
                }

                $controllerNs = 'plugins';
            }

            require $fullpath;
            break;
        }

        if (!$routeExists) {
            self::redirect(BASE_URL . "/errors/error404", 404);
            return;
        }

        // Execute The Route
        try {
            $classname = "leantime\\" . $controllerNs . "\\controllers\\" . $actionName;
            $method = self::getRequestMethod();
            $params = self::getRequestParams($method);

            //Setting default response code to 200, can be changed in controller
            self::setResponseCode(200);

            if (is_subclass_of($classname, "leantime\\core\\controller")) {
                new $classname($method, $params);
            // TODO: Remove else after all controllers utilze base class
            } else {
                $action = app()->make($classname);

                if (method_exists($action, $method)) {
                    $action->$method($params);
                //Use run for all other request types.
                } else if (method_exists($action, "run")) {
                    $action->run();
                }else {
                    self::redirect(BASE_URL."/errors/error404", 404);
                }
            }
        } catch (Exception $e) {
            error_log($e, 0);
            self::redirect(BASE_URL . "/errors/error500", 500);

            return;
        }

        self::$lastAction = $completeName;
    }

    /**
     * getRequestMethod - gets the current request method
     *
     * @access private
     * @return string|false
     */
    private static function getRequestMethod(): string|false
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            return strtolower($_SERVER['REQUEST_METHOD']);
        }

        return false;
    }

    /**
     * getRequestParams - gets the current request params
     *
     * @access private
     * @param  string $method
     * @return array
     */
    private static function getRequestParams($method)
    {
        if ($method == 'patch') {
            parse_str(file_get_contents("php://input"), $patch_vars);
        } else if (! in_array($method, ['patch', 'post', 'delete', 'get'])) {
            error_log("Unexpected HTTP Method: " . $method);
        }

        return match ($method) {
            'patch' => $patch_vars,
            'post' => $_POST,
            'delete', 'get' => $_GET,
            default => $_REQUEST,
        };
    }

    /**
     * includeAction - possible to include action from everywhere
     *
     * @access public
     * @param  string $completeName
     * @param  array $params
     * @return void
     */
    public static function includeAction($completeName, $params = array()): void
    {
        self::executeAction($completeName, $params);
    }


    /**
     * getActionName - split string to get actionName
     *
     * @access public
     * @param  string $completeName
     * @return string
     */
    public static function getActionName(string $completeName): string
    {
        $actionParts = explode(".", $completeName);

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
     * @param  string $completeName
     * @return string
     */
    public static function getModuleName(string $completeName): string
    {
        if ($completeName == '') {
            $completeName = self::getCurrentRoute();
        }

        $actionParts = explode(".", $completeName);

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
     */
    public static function getCurrentRoute(): string
    {
        if (isset($_REQUEST['act'])) {
            return htmlspecialchars($_REQUEST['act']);
        }

        return '';
    }

    /**
     * redirect - redirects to a given url
     *
     * @param string $url
     * @param int $http_response_code
     * @return void
     */
    public static function redirect(string $url, int $http_response_code = 303): void
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
