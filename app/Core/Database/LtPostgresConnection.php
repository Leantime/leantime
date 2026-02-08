<?php

namespace Leantime\Core\Database;

use Illuminate\Database\PostgresConnection;

/**
 * Custom PostgreSQL connection that handles MySQL-isms in the Leantime codebase.
 *
 * MySQL silently coerces empty strings to appropriate zero/null values for
 * non-string column types (datetime, integer, float). PostgreSQL does not.
 * This connection class converts empty string bindings to null so that
 * existing repository code works without modification on PostgreSQL.
 *
 * String columns that legitimately need empty strings should be defined as
 * nullable() in the SchemaBuilder so they accept null gracefully.
 */
class LtPostgresConnection extends PostgresConnection
{
    /**
     * Prepare the query bindings for execution.
     *
     * Converts empty strings to null for PostgreSQL compatibility.
     * MySQL treats '' as NULL/zero for datetime, int, and float columns,
     * but PostgreSQL rejects them with type errors.
     *
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        $bindings = parent::prepareBindings($bindings);

        foreach ($bindings as $key => $value) {
            if ($value === '') {
                $bindings[$key] = null;
            }
        }

        return $bindings;
    }
}
