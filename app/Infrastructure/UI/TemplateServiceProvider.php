<?php

namespace Leantime\Infrastructure\UI;

use Illuminate\Support\ServiceProvider;
use Leantime\Core\UI\Template;

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
