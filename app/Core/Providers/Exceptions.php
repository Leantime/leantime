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

class Exceptions extends ServiceProvider
{

    Use Eventhelpers;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(HandleExceptions::class, HandleExceptions::class);
    }

    public function boot() {

        $exceptionsHandler = $this->app->make(HandleExceptions::class);

        $exceptionsHandler->bootstrap($this->app);

    }


}
