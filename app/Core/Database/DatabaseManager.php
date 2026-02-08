<?php

namespace Leantime\Core\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseManager
{
    /**
     * Switch the database connection to use the given configuration.
     *
     * @param  array  $config  Database configuration with keys: dbHost, dbDatabase, dbUser, dbPassword
     */
    public static function switchConnection(array $config): void
    {
        try {
            $connectionName = config('database.default', 'mysql');

            // Purge existing connections
            DB::purge($connectionName);

            // Update the configuration
            config([
                "database.connections.{$connectionName}.host" => $config['dbHost'],
                "database.connections.{$connectionName}.database" => $config['dbDatabase'],
                "database.connections.{$connectionName}.username" => $config['dbUser'],
                "database.connections.{$connectionName}.password" => $config['dbPassword'],
            ]);

            // Reconnect with new configuration
            DB::reconnect($connectionName);

            // Verify connection
            DB::connection($connectionName)->getPdo();

        } catch (\Exception $e) {
            Log::error('Database connection failed: '.$e->getMessage());
            throw new \RuntimeException('Failed to establish database connection: '.$e->getMessage());
        }
    }
}
