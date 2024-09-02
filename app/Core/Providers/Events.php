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

        $this->app->singleton('events', function ($app) {
            return new Core\Events\EventDispatcher($app);
        });

        $this->booting(function () {

            //Core\Events\EventDispatcher::discover_listeners();

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
