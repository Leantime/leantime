<?php

namespace Leantime\Core\Providers;

use Illuminate\Contracts\Queue\Factory as QueueFactoryContract;
use Illuminate\Events\Dispatcher;
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
        $this->app->alias(\Leantime\Core\Events\EventDispatcher::class, 'events');

        $this->booting(function () {

            Core\Events\EventDispatcher::discover_listeners();

            /*
            foreach ($this->subscribe as $subscriber) {
                Event::subscribe($subscriber);
            }

            foreach ($this->observers as $model => $observers) {
                $model::observe($observers);
            }
            */
        });

        /*
        $this->booted(function () {
            $this->configureEmailVerification();
        });
        */

    }

    public function boot()
    {

    }
}
