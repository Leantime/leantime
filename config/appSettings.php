<?php

/**
 * appSettings class - System appSettings
 *
 */

namespace leantime\core;

class appSettings {

	/**
	 * @access private
	 * @var string - 1 debugmodus
	 */
	public $debug = 0;

	public $appVersion = "2.2.6";

	public $dbVersion = "2.1.9";



	/**
	 * __construct
	 *
	 */
	public function __construct(){
	}

	/**
	 * loadSettings - load all appSettings and set ini
	 *
	 */
	public function loadSettings($timezone, $debug = 0){

        if($timezone != '') {
            date_default_timezone_set($timezone);
        }else{
            date_default_timezone_set('America/Los_Angeles');
        }

        $this->debug = $debug;

		if($this->debug === 1){
            error_reporting(E_ALL);
			ini_set('display_errors', 1);
		}else{
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
			ini_set('display_errors', 0);
		}

		ini_set('session.use_cookies',1);
		ini_set('session.use_only_cookies',1);
        ini_set('session.cookie_httponly',1);
		ini_set('session.use_trans_sid',0);

		ini_set("log_errors", 1);
        ini_set('error_log', ROOT.'/../resources/logs/error.log');

	}

	public function getBaseURL () {

        if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) {

            $protocol = "https://";
        } else {
            $protocol = "http://";
        }

        $domainName = $_SERVER['HTTP_HOST'].'';
        return $protocol.$domainName;

	}

    public function getFullURL () {

        return $this->getBaseURL().rtrim($this->getRequestURI(),"/");

    }
	
    public function getRequestURI($baseURL  = "") {

	    //$_SERVER['REQUEST_URI'] will include the subfolder if one is set. Let's make sure to take it out
	    if($baseURL != "") {

            $trimmedBaseURL = rtrim($baseURL,"/");
            $baseURLParts = explode("/", $trimmedBaseURL);

            //We only need to update Request URI if we have a subfolder install
            if(is_array($baseURLParts) && count($baseURLParts) == 4) {

                //0: http, 1: "", 2: domain.com 3: subfolder
                $subfolderName = $baseURLParts[3];

                //Remove subfoldername from Request URI
                $requestURI = preg_replace('/^\/'.$subfolderName.'/', '', $_SERVER['REQUEST_URI']);

                return $requestURI;

            }

	    }

        return $_SERVER['REQUEST_URI'];

    }

}
