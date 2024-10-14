<?php

namespace Leantime\Core\Providers;

use Illuminate\Support\ServiceProvider;

class Theme extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\Leantime\Core\Theme::class, \Leantime\Core\Theme::class);
        $this->app->alias(\Leantime\Core\Theme::class, "themne");
    }


}
