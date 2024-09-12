<?php

namespace Leantime\Core\Providers;

use Illuminate\Support\ServiceProvider;

class Frontcontroller extends ServiceProvider
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
