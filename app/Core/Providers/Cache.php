<?php

namespace Leantime\Core\Providers;

use Illuminate\Support\ServiceProvider;

class Cache extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    private function instanceCacheManager(): \Illuminate\Contracts\Cache\Store
    {
    }
}
