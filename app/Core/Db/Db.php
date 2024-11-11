<?php

namespace Leantime\Core\Db;

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
     * @var string username for db
     */
    private string $user = '';

    /**
     * @var string password for db
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
     * __construct - connect to database and select db
     *
     * @return void
     */
    public function __construct($config = null)
    {
        if ($config == null) {
            $config = app('config');
        }

        $this->user = $config->dbUser;
        $this->password = $config->dbPassword;
        $this->databaseName = $config->dbDatabase;
        $this->host = $config->dbHost ?? '127.0.0.1';
        $this->port = $config->dbPort ?? '3306';

        try {
            $this->database = new PDO(
                dsn: "mysql:host={$this->host};port={$this->port};dbname={$this->databaseName}",
                username: $this->user,
                password: $this->password,
                options: [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4,sql_mode="NO_ENGINE_SUBSTITUTION"'],
            );
            $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->database->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            $this->database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {

            Log::error("Can't connect to db");
            Log::error($e);

            exit("Cannot connect to database");
        }
    }

    /**
     * This function will generate a pdo binding string (":editors0,:editors1,:editors2,:editors3") to be used in a PDO
     * query that uses the IN() clause, to assist in proper PDO array bindings to avoid SQL injection.
     *
     * A counted for loop is user rather than foreach with a key to avoid issues if the array passed has any
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
