<?php

namespace Leantime\Core\Providers;

use Illuminate\Cache\MemcachedConnector;
use Illuminate\Support\ServiceProvider;
use Leantime\Core\ApiRequest;
use Leantime\Core\CliRequest;
use Leantime\Core\Eventhelpers;
use Leantime\Core\Events;
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

        $this->app->alias(\Leantime\Core\Environment::class, 'config');
        $this->app->alias(\Leantime\Core\Environment::class, \Illuminate\Contracts\Config\Repository::class);
    }

    public function boot() {

        $this->app->make(\Leantime\Core\AppSettings::class)->loadSettings();

        $config = $this->app->make(\Leantime\Core\AppSettings::class);


        $this->setErrorHandler($config->debug ?? 0);

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
