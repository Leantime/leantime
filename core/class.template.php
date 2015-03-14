<?php

/**
 * Template class - Template routing
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @license	GNU/GPL, see license.txt
 *
 */

class template {

	/**
	 * @access private
	 * @var array - vars that are set in the action
	 */
	private $vars = array();

	/**
	 *
	 * @access private
	 * @var string
	 */
	private $controller = '';

	/**
	 * 
	 * @access private
	 * @var string
	 */
	private $notifcation = '';

	/**
	 * 
	 * @access private
	 * @var string
	 */
	private $notifcationType = '';

	/**
	 * @access public
	 * @var string
	 */
	public $tmpError = '';
	
	public $template = '';
	
	public $picture = array(
						'calendar'	=> 'iconfa-calendar',
						'clients' 	=> 'iconfa-group',
						'dashboard' => 'iconfa-th-large',
						'files' 	=> 'iconfa-picture',
						'leads' 	=> 'iconfa-signal',
						'messages' 	=> 'iconfa-envelope',
						'projects' 	=> 'iconfa-bar-chart',
						'setting'	=> 'iconfa-cogs',
						'tickets'	=> 'iconfa-pushpin',
						'timesheets'=> 'iconfa-table',
						'users'		=> 'iconfa-group',
						'default'	=> 'iconfa-off'	
					);

	/**
	 * __construct - get instance of frontcontroller
	 *
	 * @access public
	 * @return instance
	 */
	public function __construct() {
		$this->controller = FrontController::getInstance();
	}

	/**
	 * assign - assign variables in the action for template
	 *
	 * @param $name
	 * @param $value
	 * 
	 */
	public function assign($name, $value) {

		$this->vars[$name] = $value;

	}

	/**
	 * setError - assign errors to the template
	 * 
	 * @param $msg
	 * @param $type
	 * @return string
	 */
	 public function setNotification($msg,$type) {
	 	
	 	$this->notifcation = $msg;
		$this->notifcationType = $type;
	 }

	public function getModulePicture() {
		
		$module = frontcontroller::getModuleName($this->template);
		
		$picture = $this->picture['default'];
		if (isset($this->picture[$module])) 
			$picture = $this->picture[$module];
		
		return $picture;
	}

	/**
	 * display - display template from folder template
	 *
	 * @access public
	 * @param $template
	 * @return unknown_type
	 */
	public function display($template) {
		
		$client = new client();  
	
		$this->template = $template;
	
		//frontcontroller splits the name (actionname.modulename)
		$action = frontcontroller::getActionName($template);

		$module = frontcontroller::getModuleName($template);

		if (defined('MOBILE') === true) {
			
			$strTemplate = './includes/modules/' . $module . '/templates/mobile/' . $action.'.tpl.php';
			
		} else {
			
			$strTemplate = './includes/modules/' . $module . '/templates/' . $action.'.tpl.php';
		
		}
		
		if ((! file_exists($strTemplate)) || ! is_readable($strTemplate)) {

			echo '<p>Template kann nicht gefunden werden</p>';

		} else {
				
			//get language-File for labels
			$language = new language();
			
			$language->setModule($module);			
			
			$lang = $language->readIni();

			include($strTemplate);

		}
		
		
		
		return;
	}
	
	
	/**
	 * includeAction - possible to include Actions from erverywhere
	 *
	 * @access public
	 * @param $completeName
	 * @return object
	 */
	public function includeAction($completeName) {

		$this->controller->includeAction($completeName);

	}

	/**
	 * get - get assigned values
	 *
	 * @access public
	 * @param $name
	 * @return array
	 */
	public function get($name) {

		if (! isset($this->vars[$name])) {

			return null;
		}

		return $this->vars[$name];
	}
	
	public function getNotification() {
		
		return array('type' => $this->notifcationType, 'msg' => $this->notifcation);
	}
	
	/**
	 * displaySubmodule - display a submodule for a given module
	 * 
	 * @access public
	 * @param $alias
	 * @return template
	 */
	 public function displaySubmodule($alias) {
	 	
		$setting = new setting();
		
		if ($setting->submoduleHasRights($alias) !== FALSE) {
		
			$submodule = $setting->getSubmodule($alias);
		
			$file = 'includes/modules/'.$submodule['module'].'/templates/submodules/'.$submodule['submodule'];
			
			if (file_exists($file)) {
				
				$language = new language();
				
				$language->setModule($submodule['module']);			
				
				$lang = $language->readIni();
				
				include $file;
			} 
		}
	 }
	 
	 public function displaySubmoduleTitle($alias) {
	 	
		$setting = new setting();
		$language = new language();
		$title = '';
		
		if ($setting->submoduleHasRights($alias) !== FALSE) {
			
			$submodule = $setting->getSubmodule($alias);
			
			if ($submodule['title'] !== '') {
				
				$language->setModule($submodule['module']);
				$language->readIni();
				
				$title = $language->lang_echo($submodule['title']);
			
			} else {
				 
				$title = ucfirst(str_replace('.sub.php', $submodule['submodule']));
			
			}
		}
		
		return $title;
	 }
	
	/**
	 * displayLink
	 */ 
	public function displayLink($module, $name, $params = NULL, $attribute = NULL) {
	 	
		$mod = explode('.',$module);
		
		if(is_array($mod) === true && count($mod) == 2){
			
			$action = $mod[1];
			$module = $mod[0];
			
			$mod = $module.'/class.'.$action.'.php';
		
			$setting = new setting();
			$available = $setting->getAvailableModules($_SESSION['userdata']['role']);
			
		}else{
			
			$mod = array();
		
		}
		
		$returnLink = false;
		
		if (!empty($available) && in_array($mod, $available)!==false) {
				
			$url = "/".$module."/".$action."/";
			
			if (!empty($params))
			
				foreach ($params as $key => $value) {
					$url .= $value."/";	
				}
			
			$attr = '';
			
			if ($attribute!=NULL){
			
				foreach ($attribute as $key => $value){
					$attr .= $key." = '".$value."' ";
				}
			}
			
			$returnLink = "<a href='".$url."' ".$attr.">".$name."</a>";
		
		} 
		
		return $returnLink;
	 }
	
	public function displayNotification() {
			
		$language = new language();
		$language->setModule('notifications');
		$language->readIni();
		
		$notification = '';
		$note = $this->getNotification();
		if (!empty($note) && $note['msg'] != '' && $note['type'] != '') {
			
			$notification = "<div class='alert alert-".$note['type']."'>
								<button data-dismiss='alert' class='close' type='button'>×</button>
								<strong>"
									.ucfirst($note['type']).
								"!</strong> "
									.$language->lang_echo($note['msg'], false).
								"
							</div>";
		} 
		
		return $notification;
	}

}

?>