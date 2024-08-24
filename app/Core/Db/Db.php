<?php

namespace Leantime\Core\Db;

use Leantime\Core\Configuration\Environment;
use Leantime\Core\Console\CliRequest;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\IncomingRequest;
use PDO;
use PDOException;

/**
 * Database Class - Very simple abstraction layer for pdo connection
 *
 * @package    leantime
 * @subpackage core
 */
class Db
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
     * @param Environment $config
     * @return void
     */
    public function __construct(Environment $config)
    {
        $this->user = $config->dbUser;
        $this->password = $config->dbPassword;
        $this->databaseName = $config->dbDatabase;
        $this->host = $config->dbHost ?? "localhost";
        $this->port = $config->dbPort ?? "3306";

        try {
            $this->database = new PDO(
                dsn: "mysql:host={$this->host};port={$this->port};dbname={$this->databaseName}",
                username: $this->user,
                password: $this->password,
                options: [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4,sql_mode="NO_ENGINE_SUBSTITUTION"'],
            );
            $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->database->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        } catch (PDOException $e) {
            $newline = app()->make(IncomingRequest::class) instanceof CliRequest ? "\n" : "<br />\n";
            echo "No database connection, check your database credentials in your configuration file.$newline";
            $found_errors = [];

            if (!extension_loaded('PDO')) {
                $found_errors[] = "php-PDO is required, but not installed";
            }

            if (!extension_loaded('pdo_mysql')) {
                $found_errors[] = "php-pdo_mysql is required, but not installed";
            }

            if (! empty($found_errors)) {
                echo "Checking common issues:$newline";
                foreach ($found_errors as $error) {
                    echo "- $error$newline";
                }
            }

            report($e);

            exit();
        }
    }

    /**
     * This function will generate a pdo binding string (":editors0,:editors1,:editors2,:editors3") to be used in a PDO
     * query that uses the IN() clause, to assist in proper PDO array bindings to avoid SQL injection.
     *
     * A counted for loop is user rather than foreach with a key to avoid issues if the array passed has any
     * arbitrary keys
     *
     * @param string $name
     * @param int    $count
     * @return string
     */
    public static function arrayToPdoBindingString(string $name, int $count): string
    {
        $bindingStatement = "";
        for ($i = 0; $i < $count; $i++) {
            $bindingStatement .= ":" . $name . $i;
            if ($i != $count - 1) {
                $bindingStatement .= ",";
            }
        }

        return $bindingStatement;
    }

    /**
     * Sanitizes a string to only contain letters, numbers and underscore.
     * Used for patch statements with variable column keys values
     *
     *
     * @param string $string
     * @return string
     */
    public static function sanitizeToColumnString(string $string): string
    {
        return preg_replace("/[^a-zA-Z0-9_]/", "", $string);
    }
}
