<?php

namespace Leantime\Core\Application;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Leantime\Core\Configuration\AppSettings;

class AppServiceProvider extends ServiceProvider
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
        Vite::useBuildDirectory('dist');

        AboutCommand::add('Environment', [
            'Leantime App Version' => fn () => $this->app->make(AppSettings::class)->appVersion,
            'Leantime Db Version' => fn () => $this->app->make(AppSettings::class)->dbVersion,
        ]);

    }
}
