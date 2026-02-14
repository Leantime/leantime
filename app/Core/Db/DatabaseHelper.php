<?php

namespace Leantime\Core\Db;

use Illuminate\Database\ConnectionInterface;

/**
 * DatabaseHelper provides cross-database compatibility for common SQL functions
 *
 * This helper abstracts database-specific SQL syntax to support MySQL, PostgreSQL, and MS SQL Server.
 * It handles functions like GROUP_CONCAT, WEEK(), date functions, and other database-specific operations.
 */
class DatabaseHelper
{
    /**
     * Constructor
     *
     * @param  ConnectionInterface  $db  The database connection
     */
    public function __construct(private ConnectionInterface $db) {}

    /**
     * Generate cross-database string aggregation SQL
     *
     * Generates the appropriate SQL for concatenating strings from multiple rows:
     * - MySQL: GROUP_CONCAT(column SEPARATOR ',')
     * - PostgreSQL: STRING_AGG(CAST(column AS TEXT), ',')
     * - MS SQL: STRING_AGG(CAST(column AS NVARCHAR(MAX)), ',')
     *
     * @param  string  $column  The column name to aggregate
     * @param  string  $separator  The separator to use between values (default: ',')
     * @return string The database-specific SQL string
     *
     * @api
     */
    public function stringAggregate(string $column, string $separator = ','): string
    {
        return match ($this->db->getDriverName()) {
            'mysql' => "GROUP_CONCAT({$column} SEPARATOR '{$separator}')",
            'pgsql' => "STRING_AGG(CAST({$column} AS TEXT), '{$separator}')",
            'sqlsrv' => "STRING_AGG(CAST({$column} AS NVARCHAR(MAX)), '{$separator}')",
            default => "GROUP_CONCAT({$column} SEPARATOR '{$separator}')", // fallback to MySQL syntax
        };
    }

    /**
     * Generate cross-database week number extraction SQL
     *
     * Generates the appropriate SQL for extracting the week number from a date:
     * - MySQL: WEEK(column)
     * - PostgreSQL: EXTRACT(WEEK FROM column)::integer
     * - MS SQL: DATEPART(week, column)
     *
     * @param  string  $column  The column name containing the date
     * @return string The database-specific SQL string
     *
     * @api
     */
    public function weekNumber(string $column): string
    {
        return match ($this->db->getDriverName()) {
            'mysql' => "WEEK({$column})",
            'pgsql' => "EXTRACT(WEEK FROM {$column})::integer",
            'sqlsrv' => "DATEPART(week, {$column})",
            default => "WEEK({$column})", // fallback to MySQL syntax
        };
    }

    /**
     * Parse status group SQL strings to arrays
     *
     * Converts status group SQL strings like 'IN(0,-1,3)' to integer arrays [0, -1, 3]
     * This is used to convert legacy SQL-based status groups to Query Builder compatible arrays.
     *
     * @param  array  $statusGroupsSQL  Associative array with status group names as keys and SQL strings as values
     * @return array Associative array with status group names as keys and integer arrays as values
     *
     * @api
     */
    public function parseStatusGroups(array $statusGroupsSQL): array
    {
        $statusGroups = [];

        foreach ($statusGroupsSQL as $key => $sqlString) {
            // Match patterns like "IN(0,-1,3)" or "IN (0, -1, 3)"
            if (preg_match('/IN\s*\(([\d,\s-]+)\)/', $sqlString, $matches)) {
                // Split by comma, trim whitespace, convert to integers
                $values = explode(',', $matches[1]);
                $statusGroups[$key] = array_map(fn ($val) => (int) trim($val), $values);
            } else {
                // If pattern doesn't match, return empty array
                $statusGroups[$key] = [];
            }
        }

        return $statusGroups;
    }

    /**
     * Generate cross-database date formatting SQL
     *
     * Generates the appropriate SQL for formatting dates:
     * - MySQL: DATE_FORMAT(column, format)
     * - PostgreSQL: TO_CHAR(column, format)
     * - MS SQL: FORMAT(column, format)
     *
     * Note: The format string is converted from MySQL format to PostgreSQL/MS SQL format when needed
     *
     * @param  string  $column  The column name containing the date
     * @param  string  $format  The format string (MySQL DATE_FORMAT syntax)
     * @return string The database-specific SQL string
     *
     * @api
     */
    public function formatDate(string $column, string $format): string
    {
        return match ($this->db->getDriverName()) {
            'mysql' => "DATE_FORMAT({$column}, '{$format}')",
            'pgsql' => "TO_CHAR({$column}, '{$this->convertDateFormatToPostgres($format)}')",
            'sqlsrv' => "FORMAT({$column}, '{$this->convertDateFormatToMsSql($format)}')",
            default => "DATE_FORMAT({$column}, '{$format}')", // fallback to MySQL syntax
        };
    }

    /**
     * Convert MySQL DATE_FORMAT format string to PostgreSQL TO_CHAR format
     *
     * @param  string  $mysqlFormat  MySQL format string
     * @return string PostgreSQL format string
     */
    private function convertDateFormatToPostgres(string $mysqlFormat): string
    {
        // Common MySQL to PostgreSQL format conversions
        $conversions = [
            '%Y' => 'YYYY',  // 4-digit year
            '%y' => 'YY',    // 2-digit year
            '%m' => 'MM',    // Month number (01-12)
            '%d' => 'DD',    // Day of month (01-31)
            '%e' => 'FMDD',  // Day of month (1-31) without leading zero
            '%H' => 'HH24',  // Hour (00-23)
            '%i' => 'MI',    // Minutes (00-59)
            '%s' => 'SS',    // Seconds (00-59)
            '%W' => 'Day',   // Weekday name
            '%M' => 'Month', // Month name
        ];

        return str_replace(array_keys($conversions), array_values($conversions), $mysqlFormat);
    }

    /**
     * Convert MySQL DATE_FORMAT format string to MS SQL FORMAT format
     *
     * @param  string  $mysqlFormat  MySQL format string
     * @return string MS SQL format string
     */
    private function convertDateFormatToMsSql(string $mysqlFormat): string
    {
        // Common MySQL to MS SQL format conversions
        $conversions = [
            '%Y' => 'yyyy',  // 4-digit year
            '%y' => 'yy',    // 2-digit year
            '%m' => 'MM',    // Month number (01-12)
            '%d' => 'dd',    // Day of month (01-31)
            '%e' => 'd',     // Day of month (1-31) without leading zero
            '%H' => 'HH',    // Hour (00-23)
            '%i' => 'mm',    // Minutes (00-59)
            '%s' => 'ss',    // Seconds (00-59)
            '%W' => 'dddd',  // Weekday name
            '%M' => 'MMMM',  // Month name
        ];

        return str_replace(array_keys($conversions), array_values($conversions), $mysqlFormat);
    }

    /**
     * Generate cross-database SQL for yesterday's date
     *
     * Generates the appropriate SQL for getting yesterday's date:
     * - MySQL: DATE(NOW() - INTERVAL 1 DAY)
     * - PostgreSQL: (CURRENT_DATE - INTERVAL '1 day')::date
     * - MS SQL: CAST(DATEADD(day, -1, GETDATE()) AS DATE)
     *
     * @return string The database-specific SQL string
     *
     * @api
     */
    public function yesterdayDate(): string
    {
        return match ($this->db->getDriverName()) {
            'mysql' => 'DATE(NOW() - INTERVAL 1 DAY)',
            'pgsql' => "(CURRENT_DATE - INTERVAL '1 day')::date",
            'sqlsrv' => 'CAST(DATEADD(day, -1, GETDATE()) AS DATE)',
            default => 'DATE(NOW() - INTERVAL 1 DAY)', // fallback to MySQL syntax
        };
    }

    /**
     * Generate cross-database SQL for the current date
     *
     * Generates the appropriate SQL for getting the current date:
     * - MySQL: CURDATE()
     * - PostgreSQL: CURRENT_DATE
     * - MS SQL: CAST(GETDATE() AS DATE)
     *
     * @return string The database-specific SQL string
     *
     * @api
     */
    public function currentDate(): string
    {
        return match ($this->db->getDriverName()) {
            'mysql' => 'CURDATE()',
            'pgsql' => 'CURRENT_DATE',
            'sqlsrv' => 'CAST(GETDATE() AS DATE)',
            default => 'CURDATE()', // fallback to MySQL syntax
        };
    }

    /**
     * Generate cross-database SQL for date comparison with yesterday
     *
     * Generates the appropriate SQL for comparing a date column with yesterday's date:
     * - MySQL: DATE(column) = DATE(NOW() - INTERVAL 1 DAY)
     * - PostgreSQL: column::date = (CURRENT_DATE - INTERVAL '1 day')::date
     * - MS SQL: CAST(column AS DATE) = CAST(DATEADD(day, -1, GETDATE()) AS DATE)
     *
     * @param  string  $column  The column name containing the date
     * @return string The database-specific SQL string
     *
     * @api
     */
    public function isYesterday(string $column): string
    {
        return match ($this->db->getDriverName()) {
            'mysql' => "DATE({$column}) = DATE(NOW() - INTERVAL 1 DAY)",
            'pgsql' => "{$column}::date = (CURRENT_DATE - INTERVAL '1 day')::date",
            'sqlsrv' => "CAST({$column} AS DATE) = CAST(DATEADD(day, -1, GETDATE()) AS DATE)",
            default => "DATE({$column}) = DATE(NOW() - INTERVAL 1 DAY)", // fallback to MySQL syntax
        };
    }

    /**
     * Get the current database driver name
     *
     * @return string The driver name ('mysql', 'pgsql', 'sqlsrv', etc.)
     *
     * @api
     */
    public function getDriverName(): string
    {
        return $this->db->getDriverName();
    }

    /**
     * Generate cross-database SQL for FIND_IN_SET functionality
     *
     * Searches for a value in a comma-separated string field:
     * - MySQL: FIND_IN_SET(needle, haystack)
     * - PostgreSQL: needle = ANY(STRING_TO_ARRAY(haystack, ','))
     * - MS SQL: CHARINDEX(',' + needle + ',', ',' + haystack + ',') > 0
     *
     * @param  string  $needle  The value to search for (use '?' for parameter binding)
     * @param  string  $haystack  The column containing comma-separated values
     * @return string The database-specific SQL string
     *
     * @api
     */
    public function findInSet(string $needle, string $haystack): string
    {
        return match ($this->db->getDriverName()) {
            'mysql' => "FIND_IN_SET({$needle}, {$haystack})",
            'pgsql' => "{$needle} = ANY(STRING_TO_ARRAY({$haystack}, ','))",
            'sqlsrv' => "CHARINDEX(',' + CAST({$needle} AS NVARCHAR) + ',', ',' + {$haystack} + ',') > 0",
            default => "FIND_IN_SET({$needle}, {$haystack})", // fallback to MySQL syntax
        };
    }

    /**
     * Generate cross-database SQL for current timestamp
     *
     * Generates the appropriate SQL for the current date and time:
     * - MySQL: NOW()
     * - PostgreSQL: CURRENT_TIMESTAMP
     * - MS SQL: GETDATE()
     *
     * @return string The database-specific SQL string
     *
     * @api
     */
    public function currentTimestamp(): string
    {
        return match ($this->db->getDriverName()) {
            'mysql' => 'NOW()',
            'pgsql' => 'CURRENT_TIMESTAMP',
            'sqlsrv' => 'GETDATE()',
            default => 'NOW()', // fallback to MySQL syntax
        };
    }

    /**
     * Generate cross-database SQL for IFNULL/COALESCE functionality
     *
     * Returns the first non-null value:
     * - MySQL: IFNULL(expr, default)
     * - PostgreSQL: COALESCE(expr, default)
     * - MS SQL: COALESCE(expr, default)
     *
     * Note: COALESCE is ANSI SQL standard and works on all databases,
     * but this method is provided for explicit IFNULL replacement.
     *
     * @param  string  $expr  The expression to check for null
     * @param  string  $default  The default value if expr is null
     * @return string The database-specific SQL string
     *
     * @api
     */
    public function ifNull(string $expr, string $default): string
    {
        return match ($this->db->getDriverName()) {
            'mysql' => "IFNULL({$expr}, {$default})",
            'pgsql', 'sqlsrv' => "COALESCE({$expr}, {$default})",
            default => "IFNULL({$expr}, {$default})", // fallback to MySQL syntax
        };
    }

    /**
     * Generate cross-database SQL for IF/CASE functionality
     *
     * Replaces MySQL's IF(condition, then, else) with standard CASE WHEN:
     * - MySQL: IF(condition, then, else)
     * - PostgreSQL/MS SQL: CASE WHEN condition THEN then ELSE else END
     *
     * Note: This method always returns CASE WHEN syntax which is ANSI SQL standard
     * and works on all databases. Use this for cross-database compatibility.
     *
     * @param  string  $condition  The condition to evaluate
     * @param  string  $then  The value if condition is true
     * @param  string  $else  The value if condition is false
     * @return string The CASE WHEN SQL string (works on all databases)
     *
     * @api
     */
    public function ifThen(string $condition, string $then, string $else): string
    {
        // CASE WHEN is ANSI SQL standard and works on all databases
        return "CASE WHEN {$condition} THEN {$then} ELSE {$else} END";
    }

    /**
     * Generate cross-database CAST expression
     *
     * Maps abstract type names to database-specific CAST target types:
     * - 'text':    MySQL -> CHAR, PostgreSQL -> TEXT, MS SQL -> NVARCHAR(MAX)
     * - 'integer': MySQL -> SIGNED, PostgreSQL -> INTEGER, MS SQL -> INT
     * - 'decimal': MySQL -> DECIMAL(precision,scale), PostgreSQL/MS SQL -> NUMERIC(precision,scale)
     *
     * @param  string  $expression  The SQL expression to cast
     * @param  string  $type  The abstract type: 'text', 'integer', or 'decimal'
     * @param  int  $precision  Precision for decimal type (default: 10)
     * @param  int  $scale  Scale for decimal type (default: 2)
     * @return string The database-specific CAST expression
     *
     * @api
     */
    public function castAs(string $expression, string $type, int $precision = 10, int $scale = 2): string
    {
        $driver = $this->db->getDriverName();

        $targetType = match ($type) {
            'text' => match ($driver) {
                'mysql' => 'CHAR',
                'pgsql' => 'TEXT',
                'sqlsrv' => 'NVARCHAR(MAX)',
                default => 'CHAR',
            },
            'integer' => match ($driver) {
                'mysql' => 'SIGNED',
                'pgsql' => 'INTEGER',
                'sqlsrv' => 'INT',
                default => 'SIGNED',
            },
            'decimal' => match ($driver) {
                'mysql' => "DECIMAL({$precision},{$scale})",
                'pgsql' => "NUMERIC({$precision},{$scale})",
                'sqlsrv' => "DECIMAL({$precision},{$scale})",
                default => "DECIMAL({$precision},{$scale})",
            },
            default => throw new \InvalidArgumentException("Unsupported cast type: {$type}. Use 'text', 'integer', or 'decimal'."),
        };

        return "CAST({$expression} AS {$targetType})";
    }
}
