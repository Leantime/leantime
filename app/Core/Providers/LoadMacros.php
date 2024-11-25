<?php

namespace Leantime\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Stringable;
use Leantime\Core\Support\StringableMacros;

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
        Stringable::mixin(new StringableMacros);
    }
}
