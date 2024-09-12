<?php

namespace Leantime\Core\Bootstrap;

use Leantime\Core\Application;
use Symfony\Component\ErrorHandler\Debug;

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
     * @return void
     */
    public function bootstrap(\Illuminate\Contracts\Foundation\Application $app)
    {
        if ($app['config']->get('debug')) {

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
