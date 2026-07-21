<?php

declare(strict_types=1);

namespace Unit\app\Plugins\StrategyPro\Services;

use Leantime\Core\Db\Db;
use Leantime\Plugins\StrategyPro\Services\WorkAdapter;
use Unit\TestCase;

/**
 * Behaviors under test — the reverse flow's read half:
 *
 *   1. `adapt` returns a stable shape (scope + outcomes + completeness)
 *      whether goals are present or not.
 *   2. Classification splits goals by whether they carry a measurable
 *      target (metricType != '' AND endValue > 0). A percent goal with
 *      endValue=0 goes into `withoutTarget` — "0% of nothing" isn't a
 *      target.
 *   3. Provenance filter respects BOTH directions per §10.b:
 *      - `generated_from` (canvas item ← goal): forward-seeded, hide.
 *      - `maps_to` (goal → canvas item): reverse-seeded, hide.
 *      Re-runs of the reverse flow don't re-propose already-committed goals.
 *   4. `completeness` message adapts to candidate count without asserting
 *      numbers the reader can't reconstruct from what's on screen (§11
 *      rule 6). Empty / sparse / rich each have their own authored line.
 *
 * The SUT reads two DB tables (`zp_canvas` / `zp_canvas_items` for goals,
 * `zp_entity_relationship` for provenance) via `Db::getConnection()`. Rather
 * than mock a query-builder chain, this test uses an anonymous subclass
 * that overrides the two seams the adapter walks — `readGoalsInScope`
 * (private) via a protected extraction, and `filterAlreadyLinked` via a
 * hidden-ids injection — through a small trait-free subclass override.
 * That keeps tests fast, deterministic, and free of DB setup while still
 * exercising the aggregation + classification + completeness logic.
 */
class WorkAdapterTest extends TestCase
{
    public function test_empty_strategy_returns_empty_completeness(): void
    {
        $adapter = $this->makeAdapter(
            scope: ['strategyId' => 100, 'strategyName' => 'Fresh', 'programCount' => 0, 'projectCount' => 0, 'projectIds' => [100]],
            goals: [],
            hiddenIds: [],
        );

        $out = $adapter->adapt(100);

        $this->assertSame(0, count($out['outcomes']['withTarget']));
        $this->assertSame(0, count($out['outcomes']['withoutTarget']));
        $this->assertSame(0, $out['outcomes']['hiddenCount']);
        $this->assertSame('empty', $out['completeness']['level']);
        $this->assertStringContainsString('add goals', $out['completeness']['message']);
    }

    public function test_all_goals_hidden_reads_as_already_reflected(): void
    {
        // Every goal in scope carries a MapsTo or GeneratedFrom link — the
        // honest read isn't "you have no goals" (a lie) but "you've already
        // wired everything to a Logic Model" (the actual state).
        $goal = $this->goal(1, 'Prior goal', 'number', 100);
        $adapter = $this->makeAdapter(
            scope: ['strategyId' => 100, 'strategyName' => 'Old', 'programCount' => 1, 'projectCount' => 2, 'projectIds' => [100, 200, 201]],
            goals: [$goal],
            hiddenIds: [1],
        );

        $out = $adapter->adapt(100);

        $this->assertSame(0, count($out['outcomes']['withTarget']));
        $this->assertSame(1, $out['outcomes']['hiddenCount']);
        $this->assertSame('empty', $out['completeness']['level']);
        $this->assertStringContainsString('already reflected', $out['completeness']['message']);
    }

    public function test_classifies_by_metric_target(): void
    {
        // Four goals mix: percent w/ target, number w/ target, currency w/
        // target, empty metric type. The empty type is the "no clear target"
        // case regardless of the numeric values on it — mockup's "1 more
        // goal without a clear target" row.
        $adapter = $this->makeAdapter(
            scope: ['strategyId' => 100, 'strategyName' => 'X', 'programCount' => 2, 'projectCount' => 2, 'projectIds' => [100, 200, 300]],
            goals: [
                $this->goal(1, '60% completion rate', 'percent', 60),
                $this->goal(2, 'Reach 200 residents', 'number', 200),
                $this->goal(3, '$500K wage gains', 'currency', 500000),
                $this->goal(4, 'Strengthen partnerships', '', 0),
            ],
            hiddenIds: [],
        );

        $out = $adapter->adapt(100);

        $this->assertSame(3, count($out['outcomes']['withTarget']));
        $this->assertSame(1, count($out['outcomes']['withoutTarget']));
        $this->assertSame('Strengthen partnerships', $out['outcomes']['withoutTarget'][0]['title']);
        $this->assertSame('rich', $out['completeness']['level']);
    }

    public function test_percent_goal_with_zero_endvalue_is_no_target(): void
    {
        // A percent goal with endValue=0 is authored-but-unspecified —
        // rendering "0% target" or "100% of 0" would be nonsense. Falls
        // into the no-target bucket per the classifier contract.
        $adapter = $this->makeAdapter(
            scope: ['strategyId' => 100, 'strategyName' => 'X', 'programCount' => 0, 'projectCount' => 1, 'projectIds' => [100, 200]],
            goals: [$this->goal(1, 'Improve X somehow', 'percent', 0)],
            hiddenIds: [],
        );

        $out = $adapter->adapt(100);

        $this->assertSame(0, count($out['outcomes']['withTarget']));
        $this->assertSame(1, count($out['outcomes']['withoutTarget']));
    }

    public function test_hidden_goals_are_removed_from_candidates_but_counted(): void
    {
        // Three goals in scope; goal 2 hidden (say, MapsTo-linked from a
        // prior reverse run). Candidates show 2, hidden count shows 1 —
        // §11 rule 6: every number reconstructable from what's visible.
        $adapter = $this->makeAdapter(
            scope: ['strategyId' => 100, 'strategyName' => 'X', 'programCount' => 1, 'projectCount' => 1, 'projectIds' => [100, 200]],
            goals: [
                $this->goal(1, 'A', 'number', 10),
                $this->goal(2, 'B (hidden)', 'number', 20),
                $this->goal(3, 'C', 'number', 30),
            ],
            hiddenIds: [2],
        );

        $out = $adapter->adapt(100);
        $titles = array_map(static fn ($g) => $g['title'], $out['outcomes']['withTarget']);

        $this->assertSame(['A', 'C'], $titles);
        $this->assertSame(1, $out['outcomes']['hiddenCount']);
    }

    public function test_sparse_level_when_one_or_two_candidates(): void
    {
        $adapter = $this->makeAdapter(
            scope: ['strategyId' => 100, 'strategyName' => 'X', 'programCount' => 1, 'projectCount' => 1, 'projectIds' => [100, 200]],
            goals: [
                $this->goal(1, 'Only one', 'number', 10),
                $this->goal(2, 'Only two', 'number', 20),
            ],
            hiddenIds: [],
        );

        $out = $adapter->adapt(100);

        $this->assertSame('sparse', $out['completeness']['level']);
        $this->assertStringNotContainsString('%d', $out['completeness']['message'],
            'Message must be authored, not sprintf-ready — no unfilled placeholders in what reaches the reader.');
    }

    public function test_scope_shape_passes_through(): void
    {
        // The scope block on the response is what the intercept modal +
        // stage-rail templates read; regressing it silently truncates the
        // "3 programs, 7 goals, 12 milestones" line on the modal.
        $adapter = $this->makeAdapter(
            scope: ['strategyId' => 42, 'strategyName' => 'Named Strategy', 'programCount' => 3, 'projectCount' => 7, 'projectIds' => [42]],
            goals: [],
            hiddenIds: [],
        );

        $out = $adapter->adapt(42);

        $this->assertSame([
            'strategyId' => 42,
            'strategyName' => 'Named Strategy',
            'programCount' => 3,
            'projectCount' => 7,
        ], $out['scope']);
    }

    // ─── Test doubles ────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $scope        Fake `collectScope()` return.
     * @param  array<int, array<string, mixed>>  $goals   Fake `readGoalsInScope()` return.
     * @param  int[]  $hiddenIds                          Fake `filterAlreadyLinked()` return.
     */
    private function makeAdapter(array $scope, array $goals, array $hiddenIds): WorkAdapter
    {
        $db = $this->createMock(Db::class);

        return new class ($db, $scope, $goals, $hiddenIds) extends WorkAdapter
        {
            /**
             * @param  array<string, mixed>  $scope
             * @param  array<int, array<string, mixed>>  $goals
             * @param  int[]  $hiddenIds
             */
            public function __construct(
                Db $db,
                private array $scope,
                private array $goals,
                private array $hiddenIds,
            ) {
                parent::__construct($db);
            }

            protected function collectScope(int $strategyProjectId): array
            {
                return $this->scope;
            }

            protected function readGoalsInScope(array $projectIds): array
            {
                return $this->goals;
            }

            protected function filterAlreadyLinked(array $goalIds): array
            {
                return array_values(array_intersect($this->hiddenIds, $goalIds));
            }
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function goal(int $id, string $title, string $metricType, float $endValue): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'projectId' => 200,
            'projectName' => 'A Project',
            'rolledUp' => true,
            'metricType' => $metricType,
            'startValue' => 0.0,
            'endValue' => $endValue,
            'currentValue' => 0.0,
        ];
    }
}
