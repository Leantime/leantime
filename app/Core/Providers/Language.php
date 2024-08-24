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
        $this->app->singleton(\Leantime\Core\Language::class, \Leantime\Core\Language::class);
    }


}
