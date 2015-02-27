<?php

/**
 * Session class - login procedure
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package classes
 *
 *
 */

class session
{

	/**
	 * @access private
	 * @var static object
	 */
	private static $instance = NULL;

	/**
	 * @access private
	 * @var static string
	 */
	private static $sid = NULL;

	/**
	 * @access private
	 * @var string
	 */
	private $sessionpassword='asdfjkaadsfazgjahsbdfuweubsdft';

	/**
	 * __construct - get and test Session or make session
	 *
	 * @access private
	 * @return
	 */
	private  function __construct(){

		$config = new config();
		
		$this->sessionpassword = $config->sessionpassword;

		//Get sid
		if(isset($_COOKIE['sid']) === true){
			
			self::$sid=htmlspecialchars($_COOKIE['sid']);
			
		}

		$testSession = explode('-',self::$sid);

		if(is_array($testSession) === true && count($testSession) > 1){

			$testMD5 = md5($testSession[0].$this->sessionpassword);

			if($testMD5 !== $testSession[1]){

				self::makeSID();
					
			}
				
		}else{

			self::makeSID();

		}

		session_name("sid");

		session_id(self::$sid);
		
		$sessionCookieExpireTime=7200;
		session_set_cookie_params($sessionCookieExpireTime);
		@session_start();
		
		
		

	}

	/**
	 * getInstance - Get instance of session
	 *
	 * @access private
	 * @return object
	 */
	public static function getInstance(){

		if (self::$instance === NULL){
				
			self::$instance = new self;

		}

		return self::$instance;
	}

	/**
	 * getSID - get the sessionId
	 *
	 * @access public
	 * @return string
	 */
	public static function getSID(){

		return self::$sid;

	}

	/**
	 * makeSID - Generate SID with md5(), remote Address, time() and the password
	 *
	 * @access private
	 * @return string
	 */
	private function makeSID(){

		$tmp = md5((string)mt_rand() . $_SERVER['REMOTE_ADDR'] . time());

		self::$sid=$tmp .'-'.md5($tmp.$this->sessionpassword) ;

	}


}

/* @var string */
$singlesession = session::getInstance();

?>
