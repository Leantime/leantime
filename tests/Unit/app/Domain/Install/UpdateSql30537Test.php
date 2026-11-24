<?php

namespace Unit\app\Domain\Install;

use Leantime\Domain\Install\Repositories\Install;
use Unit\TestCase;

/**
 * update_sql_30537 turns the zp_stats story-point totals into floats.
 *
 * `zp_tickets.storypoints` is a float and the effort picker offers half points ("< 2min"), so a
 * project's point totals are frequently fractional. The integer columns were rejected by
 * PostgreSQL ("0.5"), aborting the nightly reports:dailyIngestion run; MySQL had been rounding
 * silently. Fresh installs get the float columns from SchemaBuilder.
 */
class UpdateSql30537Test extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var string[] the four story-point total columns the migration touches */
    private const POINT_COLUMNS = ['sum_points', 'sum_points_done', 'sum_points_progress', 'sum_points_open'];

    private function installer(SqliteStandInForInstalledDatabase $connection): Install
    {
        $install = (new \ReflectionClass(Install::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Install::class, 'connection'))->setValue($install, $connection);

        return $install;
    }

    private function createStatsTable(SqliteStandInForInstalledDatabase $connection): void
    {
        $columns = implode(', ', array_map(
            static fn (string $column): string => '"'.$column.'" INTEGER',
            self::POINT_COLUMNS
        ));

        $connection->statement('CREATE TABLE zp_stats ("sprintId" INTEGER, "projectId" INTEGER, '.$columns.')');
    }

    public function test_story_point_columns_stop_being_integers(): void
    {
        $connection = new SqliteStandInForInstalledDatabase;
        $this->createStatsTable($connection);

        $this->assertTrue($this->installer($connection)->update_sql_30537());

        foreach (self::POINT_COLUMNS as $column) {
            $this->assertNotSame(
                'integer',
                strtolower((string) $connection->getSchemaBuilder()->getColumnType('zp_stats', $column)),
                "$column must accept fractional story-point totals"
            );
        }
    }

    public function test_existing_totals_survive_the_change(): void
    {
        $connection = new SqliteStandInForInstalledDatabase;
        $this->createStatsTable($connection);
        $connection->table('zp_stats')->insert(['sprintId' => -1, 'projectId' => 4, 'sum_points' => 7]);

        $this->assertTrue($this->installer($connection)->update_sql_30537());

        $this->assertEquals(7, $connection->table('zp_stats')->value('sum_points'));
    }

    public function test_half_points_are_stored_after_the_change(): void
    {
        $connection = new SqliteStandInForInstalledDatabase;
        $this->createStatsTable($connection);

        $this->assertTrue($this->installer($connection)->update_sql_30537());

        $connection->table('zp_stats')->insert(['sprintId' => -1, 'projectId' => 4, 'sum_points' => 0.5]);

        $this->assertEquals(0.5, $connection->table('zp_stats')->value('sum_points'));
    }

    public function test_skipped_without_the_stats_table(): void
    {
        $this->assertTrue($this->installer(new SqliteStandInForInstalledDatabase)->update_sql_30537());
    }
}
