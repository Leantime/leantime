<?php

namespace Leantime\Infrastructure\Database;

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

        $this->app->bind(\Leantime\Infrastructure\Database\Db::class, function () {
            return new \Leantime\Infrastructure\Database\Db(app());
        });
    }
}
