<?php

namespace Leantime\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Leantime\Core;

class Events extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Core\Events\EventDispatcher::class, Core\Events\EventDispatcher::class);
    }

    public function boot()
    {

    }
}
