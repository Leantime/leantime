<?php

namespace Leantime\Core\UI;

use Illuminate\Support\ServiceProvider;

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
