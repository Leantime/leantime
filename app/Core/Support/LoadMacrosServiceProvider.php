<?php

namespace Leantime\Core\Support;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Leantime\Core\Support\String\AlphaNumeric;
use Leantime\Core\Support\String\BeautifyFilename;
use Leantime\Core\Support\String\SanitizeFilename;
use Leantime\Core\Support\String\SanitizeForLLM;
use Leantime\Core\Support\String\ToMarkdown;

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
        // Register string macros
        Str::mixin(new AlphaNumeric);
        Str::mixin(new BeautifyFilename);
        Str::mixin(new SanitizeFilename);
        Str::mixin(new SanitizeForLLM);
        Str::mixin(new ToMarkdown);
    }
}
