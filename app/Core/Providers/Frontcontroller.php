<?php

namespace Leantime\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Redirector;

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
