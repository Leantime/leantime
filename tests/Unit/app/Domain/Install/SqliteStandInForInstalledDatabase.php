<?php

namespace Unit\app\Domain\Install;

use Illuminate\Database\QueryException;
use Illuminate\Database\SQLiteConnection;

/**
 * An in-memory SQLite database standing in for an installed Leantime database in migration tests.
 *
 * updateDB() runs MySQL's `USE <database>` before migrating; SQLite has no database to switch to,
 * so that statement is accepted. The driver name comes from the connection config, so a test can
 * present the database as MySQL or MariaDB: information_schema.tables then answers with
 * $queueTableStatus, and a row-format change, which SQLite cannot make, is only recorded, or fails
 * when $rowFormatChangeFails is set. Everything else runs on SQLite for real.
 */
class SqliteStandInForInstalledDatabase extends SQLiteConnection
{
    /** @var array{table_engine: string, table_row_format: string}|null what information_schema.tables reports for the queue table */
    public ?array $queueTableStatus = null;

    public bool $rowFormatChangeFails = false;

    /** @var array<int, string> every statement run, in order, except `USE` */
    public array $statementsRun = [];

    /** @var array<int, array{query: string, bindings: array<int, mixed>}> reads of information_schema.tables */
    public array $informationSchemaReads = [];

    public function __construct(string $driver = 'sqlite', string $tablePrefix = '')
    {
        parent::__construct(new \PDO('sqlite::memory:'), 'leantime', $tablePrefix, ['driver' => $driver, 'name' => 'installed']);
    }

    /**
     * Runs a statement on SQLite, except MySQL's `USE` (accepted) and a row-format change
     * (recorded, or failed on request).
     */
    public function statement($query, $bindings = [])
    {
        if (str_starts_with($query, 'USE ')) {
            return true;
        }

        $this->statementsRun[] = $query;

        if (stripos($query, 'row_format') === false) {
            return parent::statement($query, $bindings);
        }

        if ($this->rowFormatChangeFails) {
            throw new QueryException('installed', $query, $bindings, new \PDOException('SQLSTATE[HY000]: General error: 1205 Lock wait timeout exceeded; try restarting transaction'));
        }

        return true;
    }

    /**
     * Runs a select on SQLite, except a read of information_schema.tables, which answers with
     * $queueTableStatus.
     */
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        if (stripos($query, 'information_schema.tables') === false) {
            return parent::select($query, $bindings, $useReadPdo);
        }

        $this->informationSchemaReads[] = ['query' => $query, 'bindings' => $bindings];

        return $this->queueTableStatus === null ? [] : [(object) $this->queueTableStatus];
    }
}
