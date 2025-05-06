<?php

namespace Leantime\Core\Routing\Middleware;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class RateLimiter extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\Illuminate\Cache\RateLimiter::class, function ($app) {
            return new \Illuminate\Cache\RateLimiter(Cache::store('installation'));
        });
    }
}
