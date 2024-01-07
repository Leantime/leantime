<?php

/**
 * Session class - login procedure
 *
 */

namespace Leantime\Core;

use Illuminate\Contracts\Container\BindingResolutionException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Session Class - login procedure
 *
 * @package    leantime
 * @subpackage core
 */
class Session
{
    use Eventhelpers;

    /**
     * @var static|null object
     */
    private static ?Session $instance = null;

    /**
     * @var string|static|null string
     */
    private static string|Session|null $sid = null;

    /**
     * @var string
     */
    private mixed $sessionpassword = '';

    /**
     * __construct - get and test Session or make session
     *
     * @param Environment     $config
     * @param IncomingRequest $request
     * @return void
     */
    public function __construct(
        /**
         * @var Environment
         */
        private Environment $config,
        /**
         * @var IncomingRequest
         **/
        private IncomingRequest $request
    ) {
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

        Events::add_filter_listener(
            'leantime.core.httpkernel.handle.beforeSendResponse',
            fn ($response) => tap($response, fn (Response $response) => $response->headers->setCookie(
                Cookie::create('sid')
                ->withValue(self::$sid)
                ->withExpires(time() + $config->sessionExpiration)
                ->withPath('/')
                ->withSameSite('Lax')
                ->withSecure(true)
            ))
        );
    }

    /**
     * getSID - get the sessionId
     *
     * @access public
     * @return string
     * @throws BindingResolutionException
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
        $session_string = ! $this->request instanceof CliRequest
            ? $_SERVER['REMOTE_ADDR']
            : 'cli';

        $tmp = hash('sha1', mt_rand(32, 32) . $session_string . time());

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

        Events::add_filter_listener(
            'leantime.core.httpkernel.handle.beforeSendResponse',
            fn ($response) => tap($response, fn (Response $response) => $response->headers->setCookie(
                Cookie::create('sid')
                ->withValue('')
                ->withExpires(time() - 42000)
                ->withPath('/')
                ->withSameSite('Strict')
                ->withSecure(true)
            ))
        );
    }
}
