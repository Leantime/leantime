<?php

namespace Leantime\Core\Providers;

use Illuminate\Support\ServiceProvider;

class Language extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\Leantime\Core\Language::class, function () {
            return new \Leantime\Core\Language;
        });
        //$this->app->alias(\Leantime\Core\Language::class, 'translator');
    }
}
