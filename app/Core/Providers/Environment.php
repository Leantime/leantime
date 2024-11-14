<?php

namespace Leantime\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Leantime\Core\Events\DispatchesEvents;

class Environment extends ServiceProvider
{
    use DispatchesEvents;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\Leantime\Core\Configuration\AppSettings::class, \Leantime\Core\Configuration\AppSettings::class);
        $this->app->singleton(\Leantime\Core\Configuration\Environment::class, \Leantime\Core\Configuration\Environment::class);

    }

    public function boot()
    {
        self::dispatchEvent('config_initialized');
    }
}
