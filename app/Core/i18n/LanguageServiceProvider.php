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

        // Direct singleton binding (not just alias) so Laravel's global __() helper
        // resolves Leantime's .ini-based translations via app('translator')->get().
        $this->app->singleton('translator', function ($app) {
            return $app->make(\Leantime\Core\Language::class);
        });
    }
}
