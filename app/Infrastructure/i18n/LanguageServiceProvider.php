<?php

namespace Leantime\Infrastructure\i18n;

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
        $this->app->singleton(\Leantime\Infrastructure\i18n\Language::class, function () {
            return new \Leantime\Infrastructure\i18n\Language;
        });
        // $this->app->alias(\Leantime\Core\Language::class, 'translator');
    }
}
