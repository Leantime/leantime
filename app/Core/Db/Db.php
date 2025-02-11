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
class Db extends DatabaseManager
{
    use DispatchesEvents;

    /**
     * @var string database host default: localhost
     */
    private string $host = '';

    /**
     * @var string username for database
     */
    private string $user = '';

    /**
     * @var string password for database
     */
    private string $password = '';

    /**
     * @var string database name
     */
    private string $databaseName = '';

    /**
     * @var string|int database port default: 3306
     */
    private string|int $port = '3306';

    /**
     * @var PDO database connection
     */
    public PDO $database;

    /**
     * @var ConnectionInterface Laravel database connection
     */
    private ConnectionInterface $connection;

    /**
     * __construct - connect to database and select database
     *
     * @return void
     */
    public function __construct($app, $connection = 'mysql')
    {

        // Get Laravel's database connection
        $this->connection = $app['db']->connection($connection);

        // Get the PDO connection from Laravel's connection
        try {

            $this->database = $this->connection->getPdo();

        } catch (\PDOException $e) {

            Log::error("Can't connect to database");
            throw new \Exception($e);
        }
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
