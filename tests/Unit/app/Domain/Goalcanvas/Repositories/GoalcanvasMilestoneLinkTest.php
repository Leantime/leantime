<?php

namespace Unit\app\Domain\Goalcanvas\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Unit\TestCase;

/**
 * Repository-level regression tests for the goal↔milestone link chokepoints.
 * Two contracts here are load-bearing and were previously untested (every
 * service test mocks the repository away):
 *
 *  1. addGoalMilestoneLink fails CLOSED — the same-project product rule
 *     (goal↔milestone links never cross projects) plus the live-milestone
 *     type check are enforced in real SQL here and nowhere else.
 *  2. removeGoalMilestoneLink / removeAllGoalMilestoneLinks keep the legacy
 *     milestoneId column in sync — without the clear, the column-union in
 *     getGoalsByMilestone() resurrects an explicitly unlinked goal.
 *
 * Faked connection, no DB — the fakes model each table's terminal calls.
 */
class GoalcanvasMilestoneLinkTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var array<int, array<string, mixed>> rows captured by edge inserts */
    private array $insertedEdges = [];

    /** @var array<int, array<string, mixed>> update payloads captured on zp_canvas_items */
    private array $columnUpdates = [];

    /**
     * Build a Goalcanvas repo whose dbConnection serves the given per-table
     * behavior.
     *
     * @param  int|null  $goalProjectId  value('cb.projectId') for the goal lookup (null = goal missing/foreign)
     * @param  object|null  $milestoneRow  first(['projectId']) for the milestone lookup (null = not a live milestone)
     * @param  bool  $edgeExists  exists() for the dedup check inside the insert transaction
     * @param  int  $edgeDeleteCount  delete() return for edge removals
     * @param  int  $columnUpdateCount  update() return for the legacy-column clear
     */
    private function repo(
        ?int $goalProjectId = null,
        ?object $milestoneRow = null,
        bool $edgeExists = false,
        int $edgeDeleteCount = 0,
        int $columnUpdateCount = 0
    ): Goalcanvas {
        $this->insertedEdges = [];
        $this->columnUpdates = [];
        $inserted = &$this->insertedEdges;
        $updates = &$this->columnUpdates;

        $builderFor = function (string $table) use ($goalProjectId, $milestoneRow, $edgeExists, $edgeDeleteCount, $columnUpdateCount, &$inserted, &$updates) {
            return new class($table, $goalProjectId, $milestoneRow, $edgeExists, $edgeDeleteCount, $columnUpdateCount, $inserted, $updates)
            {
                public function __construct(
                    private string $table,
                    private ?int $goalProjectId,
                    private ?object $milestoneRow,
                    private bool $edgeExists,
                    private int $edgeDeleteCount,
                    private int $columnUpdateCount,
                    array &$inserted,
                    array &$updates
                ) {
                    // Explicit reference assignment (not promotion) so the
                    // captures stay version-proof across supported PHP.
                    $this->inserted = &$inserted;
                    $this->updates = &$updates;
                }

                /** @var array<int, array<string, mixed>> */
                private array $inserted;

                /** @var array<int, array<string, mixed>> */
                private array $updates;

                public function join(...$a): static
                {
                    return $this;
                }

                public function where(...$a): static
                {
                    // Record scalar predicates so tests can pin WHICH rows an
                    // update/delete was scoped to (a fake that discards its
                    // where() arguments can't catch a lost predicate).
                    if (count($a) === 2 && is_scalar($a[1])) {
                        $this->wheres[$a[0]] = $a[1];
                    }

                    return $this;
                }

                /** @var array<string, mixed> scalar where() predicates seen by this builder */
                public array $wheres = [];

                public function lockForUpdate(): static
                {
                    return $this;
                }

                public function value($column)
                {
                    return $this->goalProjectId;
                }

                public function first($columns = ['*'])
                {
                    return $this->milestoneRow;
                }

                public function exists(): bool
                {
                    return $this->edgeExists;
                }

                public function insert($row): bool
                {
                    $this->inserted[] = $row;

                    return true;
                }

                public function delete(): int
                {
                    return $this->edgeDeleteCount;
                }

                public function update(array $values): int
                {
                    $this->updates[] = ['table' => $this->table, 'values' => $values, 'wheres' => $this->wheres];

                    return $this->columnUpdateCount;
                }
            };
        };

        $conn = Mockery::mock(ConnectionInterface::class);
        $conn->shouldReceive('table')->andReturnUsing($builderFor);
        $conn->shouldReceive('transaction')->andReturnUsing(fn (callable $fn) => $fn());

        $repo = (new \ReflectionClass(Goalcanvas::class))->newInstanceWithoutConstructor();
        $prop = new \ReflectionProperty(Goalcanvas::class, 'dbConnection');
        $prop->setAccessible(true);
        $prop->setValue($repo, $conn);

        return $repo;
    }

    // ── addGoalMilestoneLink: the fail-closed same-project chokepoint ──

    public function test_add_link_rejects_a_cross_project_milestone(): void
    {
        // Goal lives in project 1, milestone in project 2 — the product rule
        // (links never cross projects) must fail the write closed.
        $repo = $this->repo(goalProjectId: 1, milestoneRow: (object) ['projectId' => 2]);

        $this->assertFalse($repo->addGoalMilestoneLink(5, 42, 7));
        $this->assertSame([], $this->insertedEdges, 'no edge may be written for a cross-project link');
    }

    public function test_add_link_rejects_a_dead_or_non_milestone_ticket(): void
    {
        // The milestone lookup filters type='milestone' AND status<>-1 — a
        // task id or a soft-deleted milestone resolves to null.
        $repo = $this->repo(goalProjectId: 1, milestoneRow: null);

        $this->assertFalse($repo->addGoalMilestoneLink(5, 42, 7));
        $this->assertSame([], $this->insertedEdges);
    }

    public function test_add_link_rejects_an_unknown_or_non_goal_item(): void
    {
        // The goal lookup filters box='goal' + canvas type='goalcanvas' — a
        // foreign/shared-table id resolves to null (fail closed, no oracle).
        $repo = $this->repo(goalProjectId: null, milestoneRow: (object) ['projectId' => 1]);

        $this->assertFalse($repo->addGoalMilestoneLink(5, 42, 7));
        $this->assertSame([], $this->insertedEdges);
    }

    public function test_add_link_writes_a_correct_edge_for_a_same_project_milestone(): void
    {
        $repo = $this->repo(goalProjectId: 1, milestoneRow: (object) ['projectId' => 1]);

        $this->assertTrue($repo->addGoalMilestoneLink(5, 42, 7));
        $this->assertCount(1, $this->insertedEdges);
        $edge = $this->insertedEdges[0];
        $this->assertSame(5, $edge['entityA']);
        $this->assertSame('GoalItem', $edge['entityAType']);
        $this->assertSame(42, $edge['entityB']);
        $this->assertSame('Ticket', $edge['entityBType']);
        $this->assertSame('tracked_by', $edge['relationship']);
        $this->assertSame(7, $edge['createdBy']);
    }

    public function test_add_link_is_idempotent_when_the_edge_exists(): void
    {
        $repo = $this->repo(goalProjectId: 1, milestoneRow: (object) ['projectId' => 1], edgeExists: true);

        $this->assertTrue($repo->addGoalMilestoneLink(5, 42, 7), 'an existing link reports success');
        $this->assertSame([], $this->insertedEdges, 'but is not duplicated');
    }

    public function test_add_link_stores_unknown_author_as_null_not_zero(): void
    {
        $repo = $this->repo(goalProjectId: 1, milestoneRow: (object) ['projectId' => 1]);

        $repo->addGoalMilestoneLink(5, 42, 0);

        $this->assertNull($this->insertedEdges[0]['createdBy']);
    }

    // ── removeGoalMilestoneLink / removeAll: the clear-on-unlink dual-write ──

    public function test_unlink_clears_the_legacy_column_alongside_the_edge(): void
    {
        $repo = $this->repo(edgeDeleteCount: 1, columnUpdateCount: 1);

        $this->assertTrue($repo->removeGoalMilestoneLink(5, 42));
        $this->assertCount(1, $this->columnUpdates, 'the legacy milestoneId column must be cleared with the edge');
        $this->assertSame(['milestoneId' => ''], $this->columnUpdates[0]['values']);
        // The clear must be SCOPED: only the goal's row, and only when the
        // column still points at the milestone being unlinked — dropping the
        // milestoneId predicate would blank an unrelated newer link.
        $this->assertSame(5, $this->columnUpdates[0]['wheres']['id'] ?? null);
        $this->assertSame('42', $this->columnUpdates[0]['wheres']['milestoneId'] ?? null);
    }

    public function test_unlink_reports_success_when_only_the_stale_column_held_the_link(): void
    {
        // No edge row (already gone), but the legacy column still pointed at
        // the milestone — clearing it is a real unlink and must not read as a
        // failure.
        $repo = $this->repo(edgeDeleteCount: 0, columnUpdateCount: 1);

        $this->assertTrue($repo->removeGoalMilestoneLink(5, 42));
    }

    public function test_unlink_reports_failure_when_neither_store_held_the_link(): void
    {
        $repo = $this->repo(edgeDeleteCount: 0, columnUpdateCount: 0);

        $this->assertFalse($repo->removeGoalMilestoneLink(5, 42));
    }

    public function test_remove_all_links_clears_edges_and_the_legacy_column(): void
    {
        $repo = $this->repo(edgeDeleteCount: 3, columnUpdateCount: 1);

        $this->assertTrue($repo->removeAllGoalMilestoneLinks(5));
        $this->assertCount(1, $this->columnUpdates);
        $this->assertSame(['milestoneId' => ''], $this->columnUpdates[0]['values']);
    }

    public function test_remove_milestone_from_all_goals_clears_edges_and_columns(): void
    {
        $repo = $this->repo(edgeDeleteCount: 2, columnUpdateCount: 2);

        $this->assertTrue($repo->removeMilestoneFromAllGoals(42));
        $this->assertNotEmpty($this->columnUpdates, 'the milestone-delete cascade must clear matching legacy columns');
        $this->assertSame(['milestoneId' => ''], $this->columnUpdates[0]['values']);
        $this->assertSame('42', $this->columnUpdates[0]['wheres']['milestoneId'] ?? null, 'only columns pointing at THIS milestone are cleared');
    }

    public function test_remove_milestone_from_all_goals_reports_only_edge_deletions(): void
    {
        // PINS CURRENT BEHAVIOR: unlike removeGoalMilestoneLink /
        // removeAllGoalMilestoneLinks (which return deleted OR columnCleared),
        // the cascade returns only whether edges were deleted — a stale-column
        // -only cleanup reads as false. No caller branches on this today; if
        // the sibling semantics are ever unified, this test should fail and be
        // updated deliberately.
        $repo = $this->repo(edgeDeleteCount: 0, columnUpdateCount: 3);

        $this->assertFalse($repo->removeMilestoneFromAllGoals(42));
        $this->assertNotEmpty($this->columnUpdates, 'stale columns are still cleared even though the return is false');
    }
}
