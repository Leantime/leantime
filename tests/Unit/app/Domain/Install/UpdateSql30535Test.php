<?php

namespace Unit\app\Domain\Install;

use Illuminate\Database\Connection;
use Leantime\Domain\Install\Repositories\Install;
use Unit\TestCase;

/**
 * update_sql_30535 moves milestone dependencies from zp_tickets.milestoneid into depends_on rows
 * of zp_entity_relationship and clears milestoneid on milestones. To-Dos keep their milestoneid.
 */
class UpdateSql30535Test extends TestCase
{
    private function installer(Connection $connection): Install
    {
        $install = (new \ReflectionClass(Install::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Install::class, 'connection'))->setValue($install, $connection);

        return $install;
    }

    private function database(): SqliteStandInForInstalledDatabase
    {
        $connection = new SqliteStandInForInstalledDatabase;
        $connection->statement('CREATE TABLE zp_tickets (id INTEGER PRIMARY KEY, type VARCHAR(50), milestoneid INTEGER NULL)');
        $connection->statement('CREATE TABLE zp_entity_relationship (id INTEGER PRIMARY KEY AUTOINCREMENT, entityA INTEGER, entityAType VARCHAR(45), entityB INTEGER, entityBType VARCHAR(45), relationship VARCHAR(45), createdOn DATETIME NULL, createdBy INTEGER NULL, meta TEXT NULL)');

        return $connection;
    }

    /**
     * @return array<int, array<int, int>> prerequisites keyed by dependent milestone
     */
    private function dependencies(Connection $connection): array
    {
        $dependencies = [];
        foreach ($connection->table('zp_entity_relationship')->where('relationship', 'depends_on')->orderBy('id')->get() as $row) {
            $dependencies[(int) $row->entityA][] = (int) $row->entityB;
        }

        return $dependencies;
    }

    public function test_the_milestoneid_of_a_milestone_becomes_a_depends_on_row_and_is_cleared(): void
    {
        $connection = $this->database();
        $connection->table('zp_tickets')->insert([
            ['id' => 1, 'type' => 'milestone', 'milestoneid' => null],
            ['id' => 2, 'type' => 'milestone', 'milestoneid' => 1],
            ['id' => 3, 'type' => 'milestone', 'milestoneid' => 2],
            ['id' => 4, 'type' => 'task', 'milestoneid' => 2],
        ]);

        $this->assertTrue($this->installer($connection)->update_sql_30535());

        $this->assertSame([2 => [1], 3 => [2]], $this->dependencies($connection));

        $row = $connection->table('zp_entity_relationship')->where('entityA', 2)->first();
        $this->assertSame('Ticket', $row->entityAType);
        $this->assertSame('Ticket', $row->entityBType);

        $this->assertSame([], $connection->table('zp_tickets')->where('type', 'milestone')->whereNotNull('milestoneid')->pluck('id')->all());
        $this->assertSame(2, (int) $connection->table('zp_tickets')->where('id', 4)->value('milestoneid'), 'a To-Do keeps its milestone');
    }

    public function test_existing_rows_are_not_duplicated_and_other_dependencies_are_kept(): void
    {
        $connection = $this->database();
        $connection->table('zp_tickets')->insert([
            ['id' => 1, 'type' => 'milestone', 'milestoneid' => null],
            ['id' => 2, 'type' => 'milestone', 'milestoneid' => null],
            ['id' => 3, 'type' => 'milestone', 'milestoneid' => 1],
        ]);
        $connection->table('zp_entity_relationship')->insert([
            ['entityA' => 3, 'entityAType' => 'Ticket', 'entityB' => 1, 'entityBType' => 'Ticket', 'relationship' => 'depends_on'],
            ['entityA' => 3, 'entityAType' => 'Ticket', 'entityB' => 2, 'entityBType' => 'Ticket', 'relationship' => 'depends_on'],
        ]);

        $this->assertTrue($this->installer($connection)->update_sql_30535());

        $this->assertSame([3 => [1, 2]], $this->dependencies($connection));
    }

    public function test_dangling_self_and_non_milestone_dependencies_are_dropped(): void
    {
        $connection = $this->database();
        $connection->table('zp_tickets')->insert([
            ['id' => 1, 'type' => 'task', 'milestoneid' => null],
            ['id' => 2, 'type' => 'milestone', 'milestoneid' => 2],
            ['id' => 3, 'type' => 'milestone', 'milestoneid' => 99],
            ['id' => 4, 'type' => 'milestone', 'milestoneid' => 1],
        ]);

        $this->assertTrue($this->installer($connection)->update_sql_30535());

        $this->assertSame([], $this->dependencies($connection));
        $this->assertSame([], $connection->table('zp_tickets')->where('type', 'milestone')->whereNotNull('milestoneid')->pluck('id')->all());
    }

    public function test_running_it_twice_changes_nothing(): void
    {
        $connection = $this->database();
        $connection->table('zp_tickets')->insert([
            ['id' => 1, 'type' => 'milestone', 'milestoneid' => null],
            ['id' => 2, 'type' => 'milestone', 'milestoneid' => 1],
        ]);

        $this->assertTrue($this->installer($connection)->update_sql_30535());
        $this->assertTrue($this->installer($connection)->update_sql_30535());

        $this->assertSame([2 => [1]], $this->dependencies($connection));
    }

    public function test_skipped_without_the_tables(): void
    {
        $this->assertTrue($this->installer(new SqliteStandInForInstalledDatabase)->update_sql_30535());
    }
}
