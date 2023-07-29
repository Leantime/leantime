<?php

/**
 * Database Class - Very simple abstraction layer for pdo connection
 *
 */

namespace leantime\core;

use PDO;
use PDOException;
use leantime\core\eventhelpers;

class db
{
    use eventhelpers;

    /**
     * @access private
     * @var    string database host default: localhost
     */
    private $host = '';

    /**
     * @access private
     * @var    string username for db
     */
    private $user = '';

    /**
     * @access private
     * @var    string password for db
     */
    private $password = '';


    private $databaseName = '';

    /**
     * @access private
     * @var    string database port default: 3306
     */
    private $port = '3306';

    public PDO $database;

    /**
     * __construct - connect to database and select db
     *
     * @return object
     */
    public function __construct(\leantime\core\environment $config)
    {
        $this->user = $config->dbUser;
        $this->password = $config->dbPassword;
        $this->databaseName = $config->dbDatabase;
        $this->host = $config->dbHost ?? "localhost";
        $this->port = $config->dbPort ?? "3306";

        try {
            $driver_options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4,sql_mode="NO_ENGINE_SUBSTITUTION"' );
            $this->database = new PDO(
                "mysql:host={$this->host};port={$this->port};dbname={$this->databaseName}",
                $this->user,
                $this->password,
                $driver_options
            );
            $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->database->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        } catch (PDOException $e) {
            $newline = defined('LEAN_CLI') ? "\n" : "<br />\n";
            echo "No database connection, check your database credentials in your configuration file.$newline";
            echo "Checking common issues:$newline";

            if (!extension_loaded('PDO')) {
                echo "- php-PDO is required, but not installed$newline";
            }

            if (!extension_loaded('pdo_mysql')) {
                echo "- php-pdo_mysql is required, but not installed$newline";
            }

            error_log($e);

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
     * @param $name string
     * @param $count int
     * @return string
     */
    public static function arrayToPdoBindingString($name, $count)
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
     * @param string $name
     * @return string
     */
    public static function sanitizeToColumnString(string $string)
    {
        return preg_replace("/[^a-zA-Z0-9_]/", "", $string);
    }
}
