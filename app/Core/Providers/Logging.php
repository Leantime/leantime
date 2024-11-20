<?php

namespace Leantime\Core\Providers;

use Illuminate\Log;
use Illuminate\Support\ServiceProvider;

class Logging extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton('log', function ($app) {
            return new Log\LogManager($app);
        });

    }
}
