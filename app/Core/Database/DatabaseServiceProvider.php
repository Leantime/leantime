<?php

namespace Leantime\Core\Database;

use Illuminate\Database\DatabaseServiceProvider as LaravelDatabaseServiceProvider;

class DatabaseServiceProvider extends LaravelDatabaseServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register Laravel's database service first
        parent::register();

        // Register Db as a singleton with proper dependency injection
        $this->app->singleton(\Leantime\Core\Db\Db::class, function ($app) {
            return new \Leantime\Core\Db\Db($app);
        });
    }
}
