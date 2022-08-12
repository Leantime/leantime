<?php

/**
 * Frontcontroller class
 *
 */

namespace leantime\core {

    use Exception;
    use leantime\domain\controllers;
    use leantime\domain\repositories;


    class frontcontroller
    {

        /**
         * @access private
         * @var    string - rootpath (is set in index.php)
         */
        private $rootPath;

        /**
         * @access private
         * @var    object - one instance of this object
         */
        private static $instance = null;

        /**
         * @access private
         * @var    string - last action that was fired
         */
        private static $lastAction;

        /**
         * @access public
         * @var    string - fully parsed action
         */
        private static $fullAction;

        /**
         * __construct - Set the rootpath of the server
         *
         * @param $rootPath
         */
        private function __construct($rootPath)
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
         * @return
         */
        public static function dispatch($action = '', $httpResponseCode=200)
        {

            //Set action-name from request
            if(isset($_REQUEST['act'])) {

                self::$fullAction = htmlspecialchars($_REQUEST['act']);

            }

            //action parameter overrides Request['act']
            if($action !== '') {

                self::$fullAction = $action;

            }

            if(self::$fullAction != '') {


                //execute action
                try {

                    self::executeAction(self::$fullAction, array(), $httpResponseCode);

                } catch (Exception $e) {

                    echo $e->getMessage();

                }

            } else {

                self::dispatch("general.error404", 404);

            }
        }

        /**
         * executeAction - includes the class in includes/modules by the Request
         *
         * @access private
         * @param  $completeName
         * @return string|object
         */
        private static function executeAction($completeName, $params=array(), $httpResponseCode=200)
        {

            //actionname.filename
            //actionName is foldername
            $actionName = self::getActionName($completeName);

            //moduleName is filename
            $moduleName = self::getModuleName($completeName);

            //Folder doesn't exist.
            if(is_dir('../src/domain/' . $moduleName) === false || is_file('../src/domain/' . $moduleName . '/controllers/class.' . $actionName . '.php') === false) {

                self::dispatch("general.error404", 404);
                return;

            }

            //TODO: refactor to be psr 4 compliant
            require_once '../src/domain/' . $moduleName . '/controllers/class.' . $actionName . '.php';

            //Initialize Action
            try {

                $classname = "leantime\\domain\\controllers\\".$actionName;
                $action = new $classname();

                //Todo plugin controller call

                $method = self::getRequestMethod();

                http_response_code($httpResponseCode);

                if(method_exists($action, $method)) {

                    $params = self::getRequestParams($method);
                    $action->$method($params);

                }else{

                    //Use run for all other request types.
                    $action->run();

                }

            }catch(Exception $e){

                self::dispatch("general.error404", 501);
                error_log($e->getMessage(), 0);

                return;
            }

            self::$lastAction = $completeName;

        }

        private static function getRequestMethod()
        {

            if(isset($_SERVER['REQUEST_METHOD'])) {
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
                    break;
            case 'post':
                return $_POST;
                    break;
            case 'get':
                return $_GET;
                    break;
            case 'delete':
                return $_GET;
                    break;
            default:
                throw(new Exception("Unexpected HTTP Method: ".$method));
                    break;
            }

        }

        /**
         * includeAction - possible to include action from everywhere
         *
         * @access public
         * @param  $completeName
         * @return object
         */
        public static function includeAction($completeName, $params=array())
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
            if(is_array($actionParts) && count($actionParts) == 1){
                return "index";
            }elseif(is_array($actionParts) && count($actionParts) == 2){
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

            $actionParts = explode(".", $completeName);

            if(is_array($actionParts)){
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

            if(isset($_REQUEST['act'])) {
                return htmlspecialchars($_REQUEST['act']);
            }

            return '';

        }

        public static function redirect($url, $http_response_code = 303): void
        {

            header("Location:".trim($url),true, $http_response_code);
            exit();
        }

    }
}
