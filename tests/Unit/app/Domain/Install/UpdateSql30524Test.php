<?php

namespace Unit\app\Domain\Install;

use Illuminate\Database\ConnectionInterface;
use Leantime\Domain\Install\Repositories\Install;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Unit\TestCase;

/**
 * Regression tests for migration update_sql_30524 (#3686) — the load-bearing
 * backfill that copies each goal's legacy zp_canvas_items.milestoneId into a
 * `tracked_by` edge on zp_entity_relationship. A mis-backfill silently corrupts
 * goal↔milestone links for every existing install (and it already had a
 * near-miss: it shipped unregistered in $dbUpdates once), so its behavior is
 * pinned here with a faked connection — no DB.
 *
 * Covers: correct edge direction, junk/non-numeric skipped, deleted /
 * non-milestone tickets skipped, NULL author for unknown, idempotent re-run
 * (existing edge not duplicated), and the table-guard no-op.
 */
class UpdateSql30524Test extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Run update_sql_30524 against a fully-faked connection.
     *
     * @param  array<int, object>  $canvasGoals  rows shaped {id, milestoneId, author}
     * @param  int[]  $liveMilestoneIds  ticket ids that are live milestones
     * @param  array<int, array{0:int,1:int}>  $existingEdges  [goalId, milestoneId] pairs already linked
     * @return array{result: mixed, inserted: array<int, array<string, mixed>>}
     */
    private function runMigration(array $canvasGoals, array $liveMilestoneIds, array $existingEdges, bool $tablesExist = true): array
    {
        $inserted = [];
        $capture = function ($rows) use (&$inserted): void {
            foreach ($rows as $row) {
                $inserted[] = $row;
            }
        };

        $schema = Mockery::mock();
        $schema->shouldReceive('hasTable')->andReturn($tablesExist);
        $schema->shouldReceive('hasColumn')->andReturn($tablesExist);

        $conn = Mockery::mock(ConnectionInterface::class);
        $conn->shouldReceive('getSchemaBuilder')->andReturn($schema);
        $conn->shouldReceive('table')->andReturnUsing(
            fn () => $this->fakeBuilder($canvasGoals, $liveMilestoneIds, $existingEdges, $capture)
        );

        $install = (new \ReflectionClass(Install::class))->newInstanceWithoutConstructor();
        $prop = new \ReflectionProperty(Install::class, 'connection');
        $prop->setAccessible(true);
        $prop->setValue($install, $conn);

        return ['result' => $install->update_sql_30524(), 'inserted' => $inserted];
    }

    /**
     * One fake builder serves all three tables — the migration uses a distinct
     * terminal on each (chunkById on zp_canvas_items, pluck on zp_tickets,
     * get/insert on zp_entity_relationship), so there is no cross-talk.
     */
    private function fakeBuilder(array $canvasGoals, array $liveMilestoneIds, array $existingEdges, callable $capture): object
    {
        return new class($canvasGoals, $liveMilestoneIds, $existingEdges, $capture)
        {
            public function __construct(
                private array $canvasGoals,
                private array $liveMilestoneIds,
                private array $existingEdges,
                private $capture
            ) {}

            public function where(...$a): static
            {
                return $this;
            }

            public function whereNotNull(...$a): static
            {
                return $this;
            }

            public function whereIn(...$a): static
            {
                return $this;
            }

            public function select(...$a): static
            {
                return $this;
            }

            public function orderBy(...$a): static
            {
                return $this;
            }

            public function chunkById($count, $callback): bool
            {
                $callback(collect($this->canvasGoals));

                return true;
            }

            public function pluck($column)
            {
                return collect($this->liveMilestoneIds);
            }

            public function get($columns = ['*'])
            {
                return collect(array_map(
                    fn ($e) => (object) ['entityA' => $e[0], 'entityB' => $e[1]],
                    $this->existingEdges
                ));
            }

            public function insert($rows): bool
            {
                ($this->capture)($rows);

                return true;
            }
        };
    }

    private function goal(int $id, ?string $milestoneId, ?int $author): object
    {
        return (object) ['id' => $id, 'milestoneId' => $milestoneId, 'author' => $author];
    }

    public function test_backfills_a_tracked_by_edge_in_the_correct_direction(): void
    {
        $out = $this->runMigration(
            canvasGoals: [$this->goal(5, '42', 7)],
            liveMilestoneIds: [42],
            existingEdges: [],
        );

        $this->assertTrue($out['result']);
        $this->assertCount(1, $out['inserted']);
        $edge = $out['inserted'][0];
        $this->assertSame(5, $edge['entityA']);
        $this->assertSame('GoalItem', $edge['entityAType']);
        $this->assertSame(42, $edge['entityB']);
        $this->assertSame('Ticket', $edge['entityBType']);
        $this->assertSame('tracked_by', $edge['relationship']);
        $this->assertSame(7, $edge['createdBy']);
    }

    public function test_skips_junk_and_non_numeric_milestone_ids(): void
    {
        $out = $this->runMigration(
            canvasGoals: [$this->goal(6, 'abc', 1), $this->goal(7, '   ', 1), $this->goal(8, '4x', 1)],
            liveMilestoneIds: [42],
            existingEdges: [],
        );

        $this->assertSame([], $out['inserted'], 'non-numeric milestoneId values are skipped');
    }

    public function test_skips_deleted_or_non_milestone_tickets(): void
    {
        // Goal points at ticket 99, which is not in the live-milestone set
        // (deleted, or demoted to a task).
        $out = $this->runMigration(
            canvasGoals: [$this->goal(9, '99', 1)],
            liveMilestoneIds: [],
            existingEdges: [],
        );

        $this->assertSame([], $out['inserted'], 'a milestone that is not live is not backfilled');
    }

    public function test_is_idempotent_when_the_edge_already_exists(): void
    {
        $out = $this->runMigration(
            canvasGoals: [$this->goal(5, '42', 7)],
            liveMilestoneIds: [42],
            existingEdges: [[5, 42]],
        );

        $this->assertSame([], $out['inserted'], 'a re-run does not duplicate an existing edge');
    }

    public function test_unknown_author_is_stored_as_null_not_zero(): void
    {
        $out = $this->runMigration(
            canvasGoals: [$this->goal(9, '42', null)],
            liveMilestoneIds: [42],
            existingEdges: [],
        );

        $this->assertCount(1, $out['inserted']);
        $this->assertNull($out['inserted'][0]['createdBy'], 'unknown author stays NULL, never 0');
    }

    public function test_is_a_no_op_when_the_required_tables_are_absent(): void
    {
        $out = $this->runMigration(
            canvasGoals: [$this->goal(5, '42', 7)],
            liveMilestoneIds: [42],
            existingEdges: [],
            tablesExist: false,
        );

        $this->assertTrue($out['result']);
        $this->assertSame([], $out['inserted'], 'missing schema short-circuits before any write');
    }
}
