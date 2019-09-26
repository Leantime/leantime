<?php

/**
 * Session class - login procedure
 *
 */
namespace leantime\core;

class session
{

    /**
     * @access private
     * @var    static object
     */
    private static $instance = null;

    /**
     * @access private
     * @var    static string
     */
    private static $sid = null;

    /**
     * @access private
     * @var    string
     */
    private $sessionpassword='';

    /**
     * __construct - get and test Session or make session
     *
     * @access private
     * @return
     */
    private  function __construct()
    {

        $config = new config();
        
        $this->sessionpassword = $config->sessionpassword;

        //Get sid
        if(isset($_COOKIE['sid']) === true) {
            
            self::$sid=htmlspecialchars($_COOKIE['sid']);
            
        }

        $testSession = explode('-', self::$sid);

        if(is_array($testSession) === true && count($testSession) > 1) {

            $testMD5 = md5($testSession[0].$this->sessionpassword);

            if($testMD5 !== $testSession[1]) {

                self::makeSID();
                    
            }
                
        }else{

            self::makeSID();

        }
        
        
        session_name("sid");
        session_id(self::$sid);
        session_start();
        setcookie("sid", self::$sid, time()+$config->sessionExpiration, "/");

    }

    /**
     * getInstance - Get instance of session
     *
     * @access private
     * @return object
     */
    public static function getInstance()
    {

        if (self::$instance === null) {
                
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
    public static function getSID()
    {

        return self::$sid;

    }

    /**
     * makeSID - Generate SID with md5(), remote Address, time() and the password
     *
     * @access private
     * @return string
     */
    private function makeSID()
    {

        $tmp = md5((string)mt_rand() . $_SERVER['REMOTE_ADDR'] . time());

        self::$sid=$tmp .'-'.md5($tmp.$this->sessionpassword);

    }


}

/* @var string */
$singlesession = session::getInstance();
