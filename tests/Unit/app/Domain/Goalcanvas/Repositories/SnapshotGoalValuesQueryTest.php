<?php

namespace Unit\app\Domain\Goalcanvas\Repositories;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\Query\Processors\Processor;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Domain\Blueprints\Repositories\Blueprints;
use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas;
use Unit\TestCase;

/**
 * The "latest value per goal" subquery in snapshotGoalValues() is rendered on PostgreSQL, where
 * unquoted camelCase identifiers fold to lowercase (`itemId` -> `itemid`). The subquery's output
 * columns are referenced by the surrounding join, which the query builder does quote, so every
 * identifier in the raw select must be wrapped as well.
 *
 * Regression for: SQLSTATE[42703] column "itemid" does not exist, raised from the nightly
 * reports:goalValueSnapshot job on PostgreSQL installations.
 */
class SnapshotGoalValuesQueryTest extends TestCase
{
    /**
     * Builds the repository with a query builder bound to the PostgreSQL grammar. Executing a
     * select records its SQL and returns no rows, so no database is needed.
     *
     * @param  array<int, string>  $queries  Receives every executed select, in order.
     */
    private function repositoryCapturingQueries(array &$queries): Goalcanvas
    {
        $grammar = new PostgresGrammar(new PostgresConnection(fn () => null));
        $connection = $this->createMock(ConnectionInterface::class);
        $connection->method('table')->willReturnCallback(
            fn (string $table): Builder => (new Builder($connection, $grammar, new Processor))->from($table)
        );
        $connection->method('select')->willReturnCallback(
            function (string $query) use (&$queries): array {
                $queries[] = $query;

                return [];
            }
        );

        $dbHelper = $this->createMock(DatabaseHelper::class);
        $dbHelper->method('wrapColumn')->willReturnCallback(fn (string $column): string => $grammar->wrap($column));

        $repository = (new \ReflectionClass(Goalcanvas::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Goalcanvas::class, 'dbConnection'))->setValue($repository, $connection);
        (new \ReflectionProperty(Blueprints::class, 'dbHelper'))->setValue($repository, $dbHelper);

        return $repository;
    }

    public function test_latest_value_subquery_quotes_every_identifier(): void
    {
        $queries = [];
        $repository = $this->repositoryCapturingQueries($queries);

        $this->assertSame(0, $repository->snapshotGoalValues());
        $this->assertCount(2, $queries);

        $sql = $queries[1];

        // The subquery must emit the same quoted names the join references.
        $this->assertStringContainsString('select "itemId", MAX("dateRecorded") AS "maxDate"', $sql);
        $this->assertStringContainsString('"zp_goal_history"."itemId" = "latest"."itemId"', $sql);
        $this->assertStringContainsString('"zp_goal_history"."dateRecorded" = "latest"."maxDate"', $sql);
    }
}
