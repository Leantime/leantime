<?php

namespace Leantime\Core\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseManager
{
    public static function switchConnection(array $config): void
    {
        try {
            // Purge existing connections
            DB::purge('mysql');

            // Update the configuration
            config([
                'database.connections.mysql.host' => $config['dbHost'],
                'database.connections.mysql.database' => $config['dbDatabase'],
                'database.connections.mysql.username' => $config['dbUser'],
                'database.connections.mysql.password' => $config['dbPassword'],
            ]);

            // Reconnect with new configuration
            DB::reconnect('mysql');

            // Verify connection
            DB::connection('mysql')->getPdo();

        } catch (\Exception $e) {
            Log::error('Database connection failed: '.$e->getMessage());
            throw new \RuntimeException('Failed to establish database connection: '.$e->getMessage());
        }
    }
}
