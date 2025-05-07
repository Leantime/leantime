<?php

namespace Leantime\Infrastructure\Routing;

use Illuminate\Support\ServiceProvider;

class FrontcontrollerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton('frontcontroller', \Leantime\Core\Controller\Frontcontroller::class);

    }
}
