<?php

/**
 * Fileupload class - Data filuploads
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @license	GNU/GPL, see license.txt
 *
 */
class FrontController {

	/**
	 * @access private
	 * @var string - rootpath (is set in index.php)
	 */
	private $rootPath;

	/**
	 * @access private
	 * @var object - one instance of this object
	 */
	private static $instance = null;

	/**
	 * @access private
	 * @var string set the first action to fire
	 */
	private $firstAction = '';

	/**
	 * @access private
	 * @var string - last action that was fired
	 */
	private $lastAction;

	/**
	 * __construct - Set the rootpath of the server
	 *
	 * @param $rootPath
	 * @return object
	 */
	public function __construct($rootPath) {
		$this->rootPath = $rootPath;
	}

	/**
	 * getInstance - just one instance of the object is allowed (it makes no sense to have more)
	 *
	 * @access public static
	 * @param $rootPath
	 * @return object (instance)
	 */
	public static function getInstance($rootPath = null) {
			
		if (is_object(self::$instance) === false) {

			if (is_null($rootPath)) {

				throw new Exception('No root path');
					
			}

			self::$instance = new FrontController($rootPath);
		}

		return self::$instance;
	}

	/**
	 * run - executesx the action depending on Request or firstAction
	 *
	 * @access public
	 * @return
	 */
	public function run($differentFirstAction = '') {

		//Set action-name
		if(isset($_REQUEST['act'])){

			$completeName = htmlspecialchars($_REQUEST['act']);

		}else{
			
			if($differentFirstAction == ''){
				
				$completeName = $this->firstAction;
			
			}else{
				
				$completeName = $differentFirstAction;		
			
			}
		
		}


		if($completeName != ''){
			//execute action
			try {
	
				$this->executeAction($completeName);
	
			} catch (Exception $e) {
	
				echo $e->getMessage();
	
			}
		}
	}

	/**
	 * executeAction - includes the class in includes/modules by the Request
	 *
	 * @access private
	 * @param $completeName
	 * @return string|object
	 */
	private function executeAction($completeName) {
			
		//actionname.filename
		
		//actionName is foldername
		$actionName = self::getActionName($completeName);

		//moduleName is filename
		$moduleName = self::getModuleName($completeName);
		
		$defaultModules = array(
				'general/class.header.php',
				'general/class.footer.php',
				'general/class.ajaxRequest.php',
				'general/class.publicMenu.php',
				'general/class.mobileHeader.php',
				'general/class.mobileMenu.php',
				'general/class.mobileLogin.php',
				'publicContent/class.showArticle.php',
				'publicContent/class.contactForm.php',
				'clientPortal/class.clientRegistration.php',
				'clientPortal/class.getAjaxOrganisations.php',
				'clientPortal/class.forgotPassword.php'
				);
		
		$setting = new setting();
		if(isset($_SESSION['userdata']['role']) !== false){
			
			$availableUserModules = $setting->getAvailableModules($_SESSION['userdata']['role']);
			
			$availableModules = array_merge($availableUserModules, $defaultModules);
			
		}else{
				
			$availableModules = $defaultModules;
			
		}
		$settings = new settings();

		if(is_dir('./includes/modules/' . $moduleName) === false) {

			throw new Exception('No access');
		
		}elseif(in_array(''.$moduleName.'/class.' . $actionName . '.php', $availableModules) === false && (isset($_SESSION['userdata']) === false || $_SESSION['userdata']['id'] != 'x') && $actionName != ''){

			$tpl = new template();
			$tpl->display('general.error');		
			throw new Exception('No Access');
			
		}elseif(is_file('./includes/modules/' . $moduleName . '/actions/class.' . $actionName . '.php') === false) {

			$tpl = new template();
			$tpl->display('general.error');
		throw new Exception('No Access');

		}else{ // Else is not necessary - throw stops execution - but for the look...
			
			require_once('./includes/modules/' . $moduleName . '/actions/class.' . $actionName . '.php');

			//Initialize Action
			$action = new $actionName();

			if(is_object($action) === false) {
				throw new Exception('Coult not initialize action');
					
			}else{// Look at last else

				try {

					//Everything ok? run action
					$action->run();

				}catch (Exception $e) {
					
					echo $e->getMessage();

				}
					
			}
				
			$this->lastAction = $completeName;

		}
			
	}

	/**
	 * includeAction - possible to include action from everywhere
	 *
	 * @access public
	 * @param $completeName
	 * @return object
	 */
	public function includeAction($completeName) {
		$this->executeAction($completeName);
	}

	/**
	 * getActionName - split string to get actionName
	 *
	 * @access public
	 * @param $completeName
	 * @return string
	 */
	public static function getActionName($completeName) {

		return substr($completeName, strrpos($completeName, ".") + 1);

	}

	/**
	 * getModuleName - split string to get modulename
	 *
	 * @access public
	 * @param $completeName
	 * @return string
	 */
	public static function getModuleName($completeName) {

		return substr($completeName, 0, strrpos($completeName, "."));

	}

}
?>
