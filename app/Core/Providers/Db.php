<?php

namespace Leantime\Core\Providers;

use Illuminate\Cache\MemcachedConnector;
use Illuminate\Support\ServiceProvider;
use Leantime\Core\CliRequest;
use Leantime\Core\Events;
use Leantime\Core\IncomingRequest;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Oidc\Services\Oidc as OidcService;
use Leantime\Domain\Setting\Services\Setting as SettingsService;

class Db extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\Leantime\Core\Db::class, \Leantime\Core\Db::class);
    }


}
