<?php

namespace Leantime\Core\Providers;

use Illuminate\Cache\MemcachedConnector;
use Illuminate\Support\ServiceProvider;
use Leantime\Core\ApiRequest;
use Leantime\Core\CliRequest;
use Leantime\Core\Eventhelpers;
use Leantime\Core\Events;
use Leantime\Core\Exceptions\HandleExceptions;
use Leantime\Core\HtmxRequest;
use Leantime\Core\IncomingRequest;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Setting\Services\Setting as SettingsService;
use Symfony\Component\ErrorHandler\Debug;

class Environment extends ServiceProvider
{

    Use Eventhelpers;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\Leantime\Core\AppSettings::class, \Leantime\Core\AppSettings::class);
        $this->app->singleton(\Leantime\Core\Environment::class, \Leantime\Core\Environment::class);
        $this->app->bind(\Illuminate\Contracts\Debug\ExceptionHandler::class, \Leantime\Core\ExceptionHandler::class);

        $this->app->singleton(HandleExceptions::class, HandleExceptions::class);

        $this->app->alias(\Leantime\Core\Environment::class, 'config');
        $this->app->alias(\Leantime\Core\Environment::class, \Illuminate\Contracts\Config\Repository::class);

    }

    public function boot() {

        $config = $this->app->make(\Leantime\Core\Environment::class);

        if($config->debug) {
            Debug::enable();
            config(['debug' => true]);
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

        $exceptionsHandler = $this->app->make(HandleExceptions::class);
        $exceptionsHandler->bootstrap($this->app);


        self::dispatch_event('config_initialized');

    }


    /**
     * @param int $debug
     * @return void
     */
    private function setErrorHandler(int $debug): void
    {
        $incomingRequest = $this->app->make(IncomingRequest::class);

        if (
            $debug == 0
        ) {
            return;
        }

        Debug::enable();
    }


}
