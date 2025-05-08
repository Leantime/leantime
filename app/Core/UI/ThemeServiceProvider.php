<?php

namespace Leantime\Core\UI;

use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\Leantime\Core\UI\Theme::class, \Leantime\Core\UI\Theme::class);
        $this->app->alias(\Leantime\Core\UI\Theme::class, 'theme');
    }
}
