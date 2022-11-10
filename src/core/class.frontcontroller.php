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

        private $validStatusCodes = array("100","101","200","201","202","203","204","205","206","300","301","302","303","304","305","306","307","400","401","402","403","404","405","406","407","408","409","410","411","412","413","414","415","416","417","500","501","502","503","504","505");

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
        private static function executeAction($completeName, $params=array())
        {

            //actionname.filename
            //actionName is foldername
            $actionName = self::getActionName($completeName);

            //moduleName is filename
            $moduleName = self::getModuleName($completeName);

            //Folder doesn't exist.
            if((is_dir('../src/domain/'.$moduleName) === false ||
                is_file('../src/domain/'. $moduleName.'/controllers/class.'.$actionName.'.php') === false) &&
               (is_dir('../custom/domain/'.$moduleName) === false ||
                is_file('../custom/domain/'.$moduleName.'/controllers/class.'.$actionName . '.php') === false)) {

                self::dispatch("general.error404");
                return;

            }

            $customPluginPath = ROOT.'/../custom/plugins/' . $moduleName . '/controllers/class.' . $actionName.'.php';
            $customDomainPath = ROOT.'/../custom/domain/' . $moduleName . '/controllers/class.' . $actionName.'.php';
            $pluginPath = ROOT.'/../src/plugins/' . $moduleName . '/controllers/class.' . $actionName.'.php';
            $domainPath = ROOT.'/../src/domain/' . $moduleName . '/controllers/class.' . $actionName.'.php';

            $controllerNs = "domain";

            //Try plugin folder first for overrides
            if(file_exists($customPluginPath)) {
                
                $controllerNs = "plugins";
                require_once $customPluginPath;

            }elseif(file_exists($customDomainPath)) {

                require_once $customDomainPath;

            }elseif(file_exists($pluginPath)) {
                
                $controllerNs = "plugins";
                require_once $pluginPath;

            }elseif(file_exists($domainPath)) {

                require_once $domainPath;

            }else{
                self::dispatch("errors.error404", 404);
                return;
                
            }

            //Initialize Action
            try {

                $classname = "leantime\\".$controllerNs."\\controllers\\".$actionName;

                $action = new $classname();

                //Todo plugin controller call

                $method = self::getRequestMethod();

                //Setting default response code to 200, can be changed in controller
                self::setResponseCode(200);

                if(method_exists($action, $method)) {

                    $params = self::getRequestParams($method);
                    $action->$method($params);

                }else{

                    //Use run for all other request types.
                    $action->run();

                }

            }catch(Exception $e){

                error_log($e, 0);

                //This will catch most errors in php including db issues
                self::dispatch("errors.error500");

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

        public static function setResponseCode($responseCode) {

            if(is_int($responseCode)) {
                http_response_code($responseCode);
            }
        }

    }
}
