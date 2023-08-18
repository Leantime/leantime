<?php

/**
 * Session class - login procedure
 *
 */

namespace leantime\core;

use leantime\core\eventhelpers;

/**
 * Session Class - login procedure
 *
 * @package    leantime
 * @subpackage core
 */
class session
{
    use eventhelpers;

    /**
     * @var static object
     */
    private static $instance = null;

    /**
     * @var static string
     */
    private static $sid = null;

    /**
     * @var string
     */
    private $sessionpassword = '';

    /**
     * @var environment
     */
    private environment $config;

    /**
     * __construct - get and test Session or make session
     *
     * @param environment $config
     * @return void
     */
    public function __construct(environment $config)
    {
        $this->config = $config;
        $this->sessionpassword = $config->sessionpassword;

        if (session_status() == PHP_SESSION_ACTIVE) {
            return;
        }

        $maxLifeTime = ini_set('session.gc_maxlifetime', ($config->sessionExpiration * 2));
        $cookieLifetime = ini_set('session.cookie_lifetime', ($config->sessionExpiration * 2));

        //Get sid from cookie
        $testSession = false;

        if (isset($_COOKIE['sid']) === true) {
            self::$sid = htmlspecialchars($_COOKIE['sid']);
            $testSession = explode('-', self::$sid);
        }

        //Don't allow session ids from user.
        if (is_array($testSession) === true && count($testSession) > 1) {
            $testMD5 = hash('sha1', $testSession[0] . $this->sessionpassword);

            if ($testMD5 !== $testSession[1]) {
                self::makeSID();
            }
        } else {
            self::makeSID();
        }

        session_name("sid");
        session_id(self::$sid);
        session_start();

        setcookie("sid", self::$sid, [
            'expires' => time() + $config->sessionExpiration,
            'path' => '/',
            'samesite' => 'lax',
            'secure' => true
        ]);
    }

    /**
     * getSID - get the sessionId
     *
     * @access public
     * @return string
     */
    public static function getSID(): string
    {
        return app()->make(self::class)::$sid;
    }

    /**
     * makeSID - Generate SID with md5(), remote Address, time() and the password
     *
     * @access private
     * @return void
     */
    private function makeSID(): void
    {
        $session_string = ! defined('LEAN_CLI') || LEAN_CLI === false
            ? $_SERVER['REMOTE_ADDR']
            : 'cli';

        $tmp = hash('sha1', (string) mt_rand(32, 32) . $session_string . time());

        self::$sid = $tmp . '-' . hash('sha1', $tmp . $this->sessionpassword);
    }

    /**
     * destroySession - destroy the session
     *
     * @access public
     * @return void
     */
    public static function destroySession(): void
    {
        if (isset($_COOKIE['sid'])) {
            unset($_COOKIE['sid']);
        }

        setcookie('sid', "", [
        'expires' => time() - 42000,
        'path' => '/',
        'secure' => true,
        'samesite' => 'Strict'
        ]);
    }
}
