<?php

namespace Leantime\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Redirector;
use Leantime\Core\Template;

class TemplateServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Template::class, Template::class);
    }

}
