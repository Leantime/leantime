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
 * non-milestone tickets skipped, SAME-PROJECT enforcement (a legacy
 * cross-project row is never promoted to an edge), NULL author for unknown,
 * idempotent re-run (existing edge not duplicated), and the table-guard no-op.
 */
class UpdateSql30524Test extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Run update_sql_30524 against a fully-faked, TABLE-AWARE connection.
     *
     * @param  array<int, object>  $canvasGoals  rows shaped {id, milestoneId, author, canvasId}
     * @param  array<int, int>  $liveMilestones  ticket id => projectId for live milestones
     * @param  array<int, int>  $canvasProjects  canvas id => projectId
     * @param  array<int, array{0:int,1:int}>  $existingEdges  [goalId, milestoneId] pairs already linked
     * @param  bool  $tablesExist  Schema-guard toggle: false makes hasTable/hasColumn report missing tables (the no-op path)
     * @return array{result: mixed, inserted: array<int, array<string, mixed>>}
     */
    private function runMigration(
        array $canvasGoals,
        array $liveMilestones,
        array $canvasProjects,
        array $existingEdges,
        bool $tablesExist = true
    ): array {
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
            fn (string $table) => $this->fakeBuilder($table, $canvasGoals, $liveMilestones, $canvasProjects, $existingEdges, $capture)
        );

        $install = (new \ReflectionClass(Install::class))->newInstanceWithoutConstructor();
        $prop = new \ReflectionProperty(Install::class, 'connection');
        $prop->setAccessible(true);
        $prop->setValue($install, $conn);

        return ['result' => $install->update_sql_30524(), 'inserted' => $inserted];
    }

    /**
     * The builder is table-aware: the migration reads goal rows (chunkById on
     * zp_canvas_items), live milestones with their project (get on zp_tickets),
     * goal projects via canvases (get on zp_canvas), and existing edges
     * (get on zp_entity_relationship) — each table serves its own shape.
     */
    private function fakeBuilder(
        string $table,
        array $canvasGoals,
        array $liveMilestones,
        array $canvasProjects,
        array $existingEdges,
        callable $capture
    ): object {
        return new class($table, $canvasGoals, $liveMilestones, $canvasProjects, $existingEdges, $capture)
        {
            public function __construct(
                private string $table,
                private array $canvasGoals,
                private array $liveMilestones,
                private array $canvasProjects,
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

            public function get($columns = ['*'])
            {
                if ($this->table === 'zp_tickets') {
                    return collect(array_map(
                        fn ($id, $projectId) => (object) ['id' => $id, 'projectId' => $projectId],
                        array_keys($this->liveMilestones),
                        array_values($this->liveMilestones)
                    ));
                }
                if ($this->table === 'zp_canvas') {
                    return collect(array_map(
                        fn ($id, $projectId) => (object) ['id' => $id, 'projectId' => $projectId],
                        array_keys($this->canvasProjects),
                        array_values($this->canvasProjects)
                    ));
                }

                // zp_entity_relationship — the existing-edge dedup read.
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

    private function goal(int $id, ?string $milestoneId, ?int $author, int $canvasId = 1): object
    {
        return (object) ['id' => $id, 'milestoneId' => $milestoneId, 'author' => $author, 'canvasId' => $canvasId];
    }

    public function test_backfills_a_tracked_by_edge_in_the_correct_direction(): void
    {
        $out = $this->runMigration(
            canvasGoals: [$this->goal(5, '42', 7)],
            liveMilestones: [42 => 1],
            canvasProjects: [1 => 1],
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
            liveMilestones: [42 => 1],
            canvasProjects: [1 => 1],
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
            liveMilestones: [],
            canvasProjects: [1 => 1],
            existingEdges: [],
        );

        $this->assertSame([], $out['inserted'], 'a milestone that is not live is not backfilled');
    }

    public function test_skips_cross_project_legacy_rows(): void
    {
        // Product rule: goal↔milestone links are SAME-PROJECT only. A legacy
        // column row pointing at another project's milestone must not be
        // promoted into a first-class edge (goal's canvas 1 -> project 1;
        // milestone 42 lives in project 2).
        $out = $this->runMigration(
            canvasGoals: [$this->goal(5, '42', 7, canvasId: 1)],
            liveMilestones: [42 => 2],
            canvasProjects: [1 => 1],
            existingEdges: [],
        );

        $this->assertSame([], $out['inserted'], 'a cross-project legacy row is never promoted to an edge');
    }

    public function test_migrates_same_project_rows_alongside_skipped_cross_project_ones(): void
    {
        // Mixed chunk: goal 5's milestone is same-project (migrates), goal 6's
        // is cross-project (skipped) — the guard is per-pair, not per-chunk.
        $out = $this->runMigration(
            canvasGoals: [$this->goal(5, '42', 7, canvasId: 1), $this->goal(6, '43', 7, canvasId: 1)],
            liveMilestones: [42 => 1, 43 => 2],
            canvasProjects: [1 => 1],
            existingEdges: [],
        );

        $this->assertCount(1, $out['inserted']);
        $this->assertSame(5, $out['inserted'][0]['entityA']);
        $this->assertSame(42, $out['inserted'][0]['entityB']);
    }

    public function test_is_idempotent_when_the_edge_already_exists(): void
    {
        $out = $this->runMigration(
            canvasGoals: [$this->goal(5, '42', 7)],
            liveMilestones: [42 => 1],
            canvasProjects: [1 => 1],
            existingEdges: [[5, 42]],
        );

        $this->assertSame([], $out['inserted'], 'a re-run does not duplicate an existing edge');
    }

    public function test_unknown_author_is_stored_as_null_not_zero(): void
    {
        $out = $this->runMigration(
            canvasGoals: [$this->goal(9, '42', null)],
            liveMilestones: [42 => 1],
            canvasProjects: [1 => 1],
            existingEdges: [],
        );

        $this->assertCount(1, $out['inserted']);
        $this->assertNull($out['inserted'][0]['createdBy'], 'unknown author stays NULL, never 0');
    }

    public function test_is_a_no_op_when_the_required_tables_are_absent(): void
    {
        $out = $this->runMigration(
            canvasGoals: [$this->goal(5, '42', 7)],
            liveMilestones: [42 => 1],
            canvasProjects: [1 => 1],
            existingEdges: [],
            tablesExist: false,
        );

        $this->assertTrue($out['result']);
        $this->assertSame([], $out['inserted'], 'missing schema short-circuits before any write');
    }
}
