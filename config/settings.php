<?php

/**
 * settings class - System settings
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @license	GNU/GPL, see license.txt
 *
 */

class settings {

	/**
	 * @access private
	 * @var string - 1 debugmodus
	 */
	private $debug = '1';

	/**
	 * __construct
	 *
	 */
	public function __construct(){
	}

	/**
	 * loadSettings - load all settings and set ini
	 *
	 */
	public function loadSettings(){
		
		date_default_timezone_set('America/Los_Angeles');

		if($this->debug == 1){
				
			ini_set('display_errors', TRUE);
			error_reporting(E_ALL);
		}else{

			ini_set('display_errors', FALSE);

		}

		ini_set('session.use_cookies',1);
		ini_set('session.use_only_cookies',1);
		ini_set('session.use_trans_sid',0);
				
		ini_set("log_errors", 1);
		ini_set("error_log", "logs/error.log");
		

	}

}

$settings = new settings();
$settings->loadSettings();

?>
