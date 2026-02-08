<?php

namespace Leantime\Core\Db;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Events\DispatchesEvents;
use PDO;

/**
 * Database Class - Very simple abstraction layer for pdo connection
 */
class Db
{
    use DispatchesEvents;

    /**
     * @var ConnectionInterface Laravel database connection
     */
    private ConnectionInterface $connection;

    /**
     * @var DatabaseManager Laravel's database manager
     */
    private DatabaseManager $dbManager;

    /**
     * __construct - connect to database and select database
     *
     * @param  object  $app  Application container
     * @param  string|null  $connection  Connection name (defaults to configured default connection)
     * @return void
     */
    public function __construct($app, ?string $connection = null)
    {
        // Get Laravel's database manager from the container
        $this->dbManager = $app['db'];

        // Use the configured default connection if none specified
        $connection = $connection ?? $app['config']->get('database.default', 'mysql');

        // Get a connection from the manager
        try {
            $this->connection = $this->dbManager->connection($connection);
        } catch (\PDOException $e) {
            Log::error("Can't connect to database");
            throw new \Exception($e);
        }
    }

    /**
     * Get the PDO connection (lazily retrieved from Laravel's connection pool)
     *
     * @return PDO
     */
    public function __get($name)
    {
        if ($name === 'database') {
            return $this->connection->getPdo();
        }

        return null;
    }

    /**
     * Get the Laravel ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * This function will generate a PDO binding string (":editors0,:editors1,:editors2,:editors3") to be used in a PDO
     * query that uses the IN() clause, to assist in proper PDO array bindings to avoid SQL injection.
     *
     * A counted for loop is used rather than foreach with a key to avoid issues if the array passed has any
     * arbitrary keys
     */
    public static function arrayToPdoBindingString(string $name, int $count): string
    {
        $bindingStatement = '';
        for ($i = 0; $i < $count; $i++) {
            $bindingStatement .= ':'.$name.$i;
            if ($i != $count - 1) {
                $bindingStatement .= ',';
            }
        }

        return $bindingStatement;
    }

    /**
     * Sanitizes a string to only contain letters, numbers and underscore.
     * Used for patch statements with variable column keys values
     */
    public static function sanitizeToColumnString(string $string): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $string);
    }

    public static function sanitizeComparitorString(string $string): string
    {
        return preg_replace('/[^=<>LIKENOT]/', '', $string);
    }
}
