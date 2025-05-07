<?php

namespace Leantime\Infrastructure\Plugins;

use Illuminate\Support\ServiceProvider;

class PluginsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\Leantime\Domain\Plugins\Services\Plugins::class, \Leantime\Domain\Plugins\Services\Plugins::class);
    }
}
