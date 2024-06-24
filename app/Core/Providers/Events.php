<?php

namespace Leantime\Core\Providers;

use Closure;
use Illuminate\Cache\MemcachedConnector;
use Illuminate\Support\ServiceProvider;
use Leantime\Core\CliRequest;
use Leantime\Core;
use Leantime\Core\IncomingRequest;
use Leantime\Domain\Setting\Services\Setting as SettingsService;

class Events extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Core\Events::class, Core\Events::class);
    }

    public function boot()
    {

    }
}
