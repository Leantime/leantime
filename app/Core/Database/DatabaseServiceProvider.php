<?php

namespace Leantime\Core\Database;

use Illuminate\Database\Connection;
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

        // Register custom PostgreSQL connection that handles empty-string-to-null conversion.
        // MySQL silently coerces '' to NULL/zero for non-string columns; PostgreSQL does not.
        Connection::resolverFor('pgsql', function ($connection, $database, $prefix, $config) {
            return new LtPostgresConnection($connection, $database, $prefix, $config);
        });

        // Register Db as a singleton with proper dependency injection
        app()->singleton(\Leantime\Core\Db\Db::class, function ($app) {
            return new \Leantime\Core\Db\Db($app);
        });
    }
}
