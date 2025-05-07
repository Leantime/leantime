<?php

namespace Leantime\Infrastructure\Support;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class LoadMacrosServiceProvider extends ServiceProvider
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
