<?php

namespace Leantime\Core\Bootstrap;

use ErrorException;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Log\LogManager;
use Illuminate\Support\Env;
use Leantime\Core\Application;
use Monolog\Handler\NullHandler;
use PHPUnit\Runner\ErrorHandler;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Throwable;

class HandleExceptions extends \Illuminate\Foundation\Bootstrap\HandleExceptions
{

    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected static $app;

    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(\Illuminate\Contracts\Foundation\Application $app)
    {
        if($app['config']->get("debug")) {

            Debug::enable();
            config(['debug_blacklist' => [
                '_ENV' => [
                    'LEAN_EMAIL_SMTP_PASSWORD',
                    'LEAN_DB_PASSWORD',
                    'LEAN_SESSION_PASSWORD',
                    'LEAN_OIDC_CLIEND_SECRET',
                    'LEAN_S3_SECRET',
                ],

                '_SERVER' => [
                    'LEAN_EMAIL_SMTP_PASSWORD',
                    'LEAN_DB_PASSWORD',
                    'LEAN_SESSION_PASSWORD',
                    'LEAN_OIDC_CLIEND_SECRET',
                    'LEAN_S3_SECRET',
                ],
                '_POST' => [
                    'password',
                ],
            ]]);
        }

       parent::bootstrap($app);

    }


}
