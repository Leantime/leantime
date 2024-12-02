<?php

namespace Leantime\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Leantime\Core\Support\StrMacros;

class LoadMacros extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Str::mixin(new StrMacros);
    }
}
