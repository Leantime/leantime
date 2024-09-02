<?php

namespace Leantime\Core\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class Plugins extends ServiceProvider
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
