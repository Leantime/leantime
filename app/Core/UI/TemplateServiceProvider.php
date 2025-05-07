<?php

namespace Leantime\Core\UI;

use Illuminate\Support\ServiceProvider;
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
