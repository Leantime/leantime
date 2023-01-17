<?php

/**
 * Frontcontroller class
 *
 */

namespace leantime\core {

    use Exception;

    class frontcontroller
    {
        use eventhelpers;

        /**
         * @access private
         * @var    string - rootpath (is set in index.php)
         */
        private string $rootPath = '';

        /**
         * @access private
         * @var    frontcontroller|null - one instance of this object
         */
        private static frontcontroller|null $instance = null;

        /**
         * @access private
         * @var    string - last action that was fired
         */
        private static string $lastAction = '';

        /**
         * @access public
         * @var    string - fully parsed action
         */
        private static string $fullAction = '';

        private array $validStatusCodes = array("100","101","200","201","202","203","204","205","206","300","301","302","303","304","305","306","307","400","401","402","403","404","405","406","407","408","409","410","411","412","413","414","415","416","417","500","501","502","503","504","505");

        /**
         * __construct - Set the rootpath of the server
         *
         * @param $rootPath
         */
        private function __construct(string $rootPath)
        {
            $this->rootPath = $rootPath;
        }

        /**
         * getInstance - just one instance of the object is allowed (it makes no sense to have more)
         *
         * @access public static
         * @param  $rootPath
         * @return object (instance)
         */
        public static function getInstance($rootPath = null)
        {

            if (is_object(self::$instance) === false) {
                if (is_null($rootPath)) {
                    throw new Exception('No root path');
                }

                self::$instance = new frontcontroller($rootPath);
            }

            return self::$instance;
        }

        /**
         * run - executes the action depending on Request or firstAction
         *
         * @access public
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
         * @return void
         */
        private static function executeAction($completeName, $params = array()): void
        {

            $actionName = self::getActionName($completeName); //actionName is foldername
            $moduleName = self::getModuleName($completeName); //moduleName is filename
            $controllerNs = "domain";

            // initialize plugin service to check
            if ($_SESSION['isInstalled'] === true && $_SESSION['isUpdated'] === true) {
                $pluginService = new \leantime\domain\services\plugins();
            }

            // Check If Route Exists And Fetch Right Route Based On Priority
            $routeExists = false;
            foreach (
                [
                'custom/plugins',
                'custom/domain',
                'plugins',
                'domain'
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
                self::dispatch("errors.error404", 404);
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
                    $action = new $classname();

                    if (method_exists($action, $method)) {
                        $action->$method($params);
                    //Use run for all other request types.
                    } else {
                        $action->run();
                    }
                }
            } catch (Exception $e) {
                error_log($e, 0);

                //This will catch most errors in php including db issues
                self::dispatch("errors.error500");

                return;
            }

            self::$lastAction = $completeName;
        }

        private static function getRequestMethod()
        {

            if (isset($_SERVER['REQUEST_METHOD'])) {
                return strtolower($_SERVER['REQUEST_METHOD']);
            }

            return false;
        }

        private static function getRequestParams($method)
        {

            switch ($method) {
                case 'patch':
                    parse_str(file_get_contents("php://input"), $patch_vars);
                    return $patch_vars;
                case 'post':
                    return $_POST;
                case 'delete':
                case 'get':
                    return $_GET;
                default:
                    throw(new Exception("Unexpected HTTP Method: " . $method));
            }
        }

        /**
         * includeAction - possible to include action from everywhere
         *
         * @access public
         * @param  $completeName
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
         * @param  $completeName
         * @return string
         */
        public static function getActionName($completeName): string
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
         * @param  $completeName
         * @return string
         */
        public static function getModuleName($completeName)
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
        public static function getCurrentRoute()
        {

            if (isset($_REQUEST['act'])) {
                return htmlspecialchars($_REQUEST['act']);
            }

            return '';
        }

        public static function redirect($url, $http_response_code = 303): void
        {

            header("Location:" . trim($url), true, $http_response_code);
            exit();
        }

        public static function setResponseCode($responseCode)
        {

            if (is_int($responseCode)) {
                http_response_code($responseCode);
            }
        }
    }
}
