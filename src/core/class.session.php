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

        ini_set('session.gc_maxlifetime', ($config->sessionExpiration*2));

        $this->sessionpassword = $config->sessionpassword;

        //Get sid from cookie
        $testSession = false;

        if(isset($_COOKIE['sid']) === true) {

            self::$sid=htmlspecialchars($_COOKIE['sid']);
            $testSession = explode('-', self::$sid);

        }else if($this->getBearerToken() === true) {

            self::$sid=htmlspecialchars($this->getBearerToken());
            $testSession = explode('-', self::$sid);

        }

        //Don't allow session ids from user.
        if(is_array($testSession) === true && count($testSession) > 1) {

            $testMD5 = hash('sha1', $testSession[0].$this->sessionpassword);

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
                
            self::$instance = new self();

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

        $tmp = hash('sha1', (string)mt_rand(32,32) . $_SERVER['REMOTE_ADDR'] . time());

        self::$sid=$tmp .'-'.hash('sha1', $tmp.$this->sessionpassword);

    }


    /**
     * Get hearder Authorization
     * */
    public function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else {
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
                $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
            } elseif (function_exists('apache_request_headers')) {
                $requestHeaders = apache_request_headers();
                // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
                $requestHeaders = array_combine(
                    array_map('ucwords', array_keys($requestHeaders)),
                    array_values($requestHeaders)
                );
                //print_r($requestHeaders);
                if (isset($requestHeaders['Authorization'])) {
                    $headers = trim($requestHeaders['Authorization']);
                }
            }
        }
        return $headers;
    }

    /**
     * get access token from header
     * */
    public function getBearerToken()
    {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

}

/* @var string */
$singlesession = session::getInstance();
