<?php

namespace Leantime\Core\i18n;

use Illuminate\Support\ServiceProvider;

class LanguageServiceProvider extends ServiceProvider
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
        // $this->app->alias(\Leantime\Core\Language::class, 'translator');
    }
}
