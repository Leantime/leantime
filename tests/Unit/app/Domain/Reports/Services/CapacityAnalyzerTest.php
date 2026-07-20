<?php

declare(strict_types=1);

namespace Unit\app\Domain\Reports\Services;

use Leantime\Core\Resources\Models\PersonAllocation;
use Leantime\Core\Resources\Models\ResourceSummary;
use Leantime\Domain\Reports\Models\ReportPeriod;
use Leantime\Domain\Reports\Services\CapacityAnalyzer;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketsRepo;
use Unit\TestCase;

/**
 * CapacityAnalyzer turns "work we have planned" (tickets) into a verdict
 * against "people we have" (ResourceSummary). Its output drives the
 * stakeholder report's capacity block and, at strategy scope, the escalated
 * gap rows — so a wrong verdict here is a wrong number in front of a
 * portfolio audience.
 *
 * Behaviors under test:
 *
 * Supply at program rollup:
 *   1. Supply is each person's CAPACITY, not the hours already allocated —
 *      the fix for the report contradicting its own headline tile.
 *   2. A person on two child projects is counted once, not twice.
 *   3. Commitments to projects OUTSIDE the program are deducted, and the
 *      deduction never drives a person's contribution negative.
 *   4. People with no allocation anywhere in the program don't contribute.
 *
 * Verdicts — the four quadrants of (demand, supply):
 *   5. demand 0                → no_work
 *   6. demand > 0, supply 0    → no_capacity, and specifically NOT critical:
 *      "nobody is assigned" is an authoring gap, not a capacity crisis.
 *   7. demand > 0, supply > 0  → healthy/tight/critical by ratio.
 *   8. demand well under supply → buffer (idle capacity).
 *
 * isDone:
 *   9. Documents the CURRENT hardcoded status <= 0 rule. Leantime DONE
 *      statuses are project-configured and typically positive, so this
 *      pins known-wrong behavior deliberately — see the test's own note.
 */
class CapacityAnalyzerTest extends TestCase
{
    // ─── Supply: capacity, not allocation ────────────────────────────

    public function test_program_supply_uses_capacity_not_allocated_hours(): void
    {
        // One person: 40h capacity, only 5h booked against the program.
        // Supply must read 40 (what they could do), not 5 (what's booked).
        $resources = $this->summary([
            $this->person(itemId: 1, capacity: 40, allocations: [7 => 5.0]),
        ]);

        $row = $this->rollup($resources, childIds: [7]);

        // 1 week period → availableHours == weekly supply.
        $this->assertSame(40.0, $row['availableHours']);
        $this->assertSame(1, $row['peopleCount']);
    }

    public function test_program_supply_counts_a_person_on_two_child_projects_once(): void
    {
        $resources = $this->summary([
            $this->person(itemId: 1, capacity: 40, allocations: [7 => 5.0, 8 => 5.0]),
        ]);

        $row = $this->rollup($resources, childIds: [7, 8]);

        $this->assertSame(40.0, $row['availableHours'], 'capacity counted once, not per project');
        $this->assertSame(1, $row['peopleCount']);
    }

    public function test_program_supply_deducts_commitments_outside_the_program(): void
    {
        // 40h capacity, 10h booked here, 15h booked on a project that is not
        // a child of this program → 25h is what this program could claim.
        $resources = $this->summary([
            $this->person(itemId: 1, capacity: 40, allocations: [7 => 10.0, 99 => 15.0]),
        ]);

        $row = $this->rollup($resources, childIds: [7]);

        $this->assertSame(25.0, $row['availableHours']);
    }

    public function test_outside_commitments_never_push_a_person_below_zero(): void
    {
        // Over-committed elsewhere beyond their own capacity. They bring
        // nothing to this program, but must not subtract from other people.
        $resources = $this->summary([
            $this->person(itemId: 1, capacity: 40, allocations: [7 => 1.0, 99 => 100.0]),
            $this->person(itemId: 2, capacity: 20, allocations: [7 => 5.0]),
        ]);

        $row = $this->rollup($resources, childIds: [7]);

        $this->assertSame(20.0, $row['availableHours'], 'clamped at 0, not -60');
    }

    public function test_people_not_allocated_to_the_program_contribute_nothing(): void
    {
        $resources = $this->summary([
            $this->person(itemId: 1, capacity: 40, allocations: [99 => 10.0]),
        ]);

        $row = $this->rollup($resources, childIds: [7]);

        $this->assertSame(0.0, $row['availableHours']);
        $this->assertSame(0, $row['peopleCount']);
    }

    // ─── Verdict quadrants ───────────────────────────────────────────

    public function test_verdict_is_no_work_when_there_is_no_demand(): void
    {
        $resources = $this->summary([
            $this->person(itemId: 1, capacity: 40, allocations: [7 => 10.0]),
        ]);

        $row = $this->rollup($resources, childIds: [7], budgetedHours: 0.0);

        $this->assertSame('no_work', $row['verdict']);
    }

    /**
     * The distinction Gloria asked for: work planned and nobody assigned is
     * NOT a capacity crisis. It is an authoring gap with a different action
     * ("assign people") and a different owner. Painting it critical trains
     * readers to ignore critical.
     */
    public function test_verdict_is_no_capacity_not_critical_when_nobody_is_assigned(): void
    {
        $resources = $this->summary([]); // nobody on the plan at all

        $row = $this->rollup($resources, childIds: [7], budgetedHours: 400.0);

        $this->assertSame('no_capacity', $row['verdict']);
        $this->assertNotSame('critical', $row['verdict']);
    }

    public function test_verdict_is_critical_when_demand_far_exceeds_supply(): void
    {
        $resources = $this->summary([
            $this->person(itemId: 1, capacity: 10, allocations: [7 => 5.0]),
        ]);

        // supply 10, demand 100 → ratio (100-10)/10 = 9 → critical
        $row = $this->rollup($resources, childIds: [7], budgetedHours: 100.0);

        $this->assertSame('critical', $row['verdict']);
        $this->assertSame(90.0, $row['gap']);
    }

    public function test_verdict_is_buffer_when_supply_far_exceeds_demand(): void
    {
        $resources = $this->summary([
            $this->person(itemId: 1, capacity: 100, allocations: [7 => 5.0]),
        ]);

        // supply 100, demand 10 → ratio (10-100)/100 = -0.9 → buffer
        $row = $this->rollup($resources, childIds: [7], budgetedHours: 10.0);

        $this->assertSame('buffer', $row['verdict']);
    }

    /**
     * The regression this whole change exists for: a program with real
     * headroom and nothing booked against it yet used to read no_capacity,
     * because supply was measured as hours-already-allocated. With capacity
     * as supply it reads as buffer — there IS room.
     */
    public function test_program_with_headroom_and_light_booking_is_not_no_capacity(): void
    {
        $resources = $this->summary([
            $this->person(itemId: 1, capacity: 40, allocations: [7 => 0.5]),
        ]);

        $row = $this->rollup($resources, childIds: [7], budgetedHours: 10.0);

        $this->assertNotSame('no_capacity', $row['verdict']);
        $this->assertSame('buffer', $row['verdict'], 'real headroom reads as buffer, not a false crisis');
    }

    // ─── isDone ──────────────────────────────────────────────────────

    /**
     * PINS KNOWN-WRONG BEHAVIOR ON PURPOSE.
     *
     * isDone() hardcodes "status <= 0 is done". Leantime DONE statuses are
     * project-configured and typically POSITIVE, so a genuinely-done ticket
     * with a positive done status is counted as open and inflates demand.
     *
     * This test documents the current contract so the fix is a deliberate,
     * visible change rather than a silent one. When isDone() is taught to
     * consult the project's configured statuses, this test SHOULD fail and
     * be rewritten — that is the point of it.
     */
    public function test_is_done_currently_treats_only_non_positive_status_as_done(): void
    {
        $tickets = [
            ['id' => 1, 'status' => -1, 'planHours' => 10, 'storypoints' => 0],  // done by this rule
            ['id' => 2, 'status' => 0,  'planHours' => 10, 'storypoints' => 0],  // done by this rule
            ['id' => 3, 'status' => 3,  'planHours' => 10, 'storypoints' => 0],  // counted OPEN
        ];

        $rows = $this->analyzer($tickets)->analyzeProjects(
            [7],
            $this->period(),
            $this->summary([$this->person(itemId: 1, capacity: 40, allocations: [7 => 10.0])]),
        );

        $this->assertSame(1, $rows[7]['openTicketCount']);
        $this->assertSame(10.0, $rows[7]['budgetedHours'],
            'a positive status is treated as open — the known isDone() gap');
    }

    // ─── Harness ─────────────────────────────────────────────────────

    /**
     * @param  array<int, array<string, mixed>>  $tickets
     */
    private function analyzer(array $tickets = []): CapacityAnalyzer
    {
        $repo = $this->createMock(TicketsRepo::class);
        $repo->method('getAllByProjectId')->willReturn($tickets);

        return new CapacityAnalyzer($repo);
    }

    /**
     * A one-week period, so weeksInPeriod is 1 and availableHours equals the
     * weekly supply figure — keeps the assertions about supply, not arithmetic.
     */
    private function period(): ReportPeriod
    {
        return ReportPeriod::fromRequest([
            'from' => '2026-01-05',
            'to' => '2026-01-12',
        ]);
    }

    /**
     * @param  array<int, PersonAllocation>  $people
     */
    private function summary(array $people): ResourceSummary
    {
        return new ResourceSummary([7, 8], $people, [], [], 0.0, 0.0, 0.0, 0.0);
    }

    /**
     * @param  array<int, float>  $allocations
     */
    private function person(int $itemId, float $capacity, array $allocations): PersonAllocation
    {
        return new PersonAllocation(
            itemId: $itemId,
            userId: $itemId,
            displayName: 'Person '.$itemId,
            capacity: $capacity,
            allocations: $allocations,
        );
    }

    /**
     * Runs aggregateByProgram for a single program with the given children,
     * feeding it a pre-built project row so the ticket side is fixed and the
     * assertions are about the capacity side.
     *
     * @param  int[]  $childIds
     * @return array<string, mixed>
     */
    private function rollup(
        ResourceSummary $resources,
        array $childIds,
        float $budgetedHours = 50.0,
    ): array {
        $projectRows = [];
        foreach ($childIds as $cid) {
            $projectRows[$cid] = [
                'projectId' => $cid,
                'name' => 'Child '.$cid,
                // Demand lives entirely on the first child so the totals are
                // predictable regardless of how many children a case uses.
                'openTicketCount' => $cid === $childIds[0] ? 10 : 0,
                'ticketsWithBudget' => $cid === $childIds[0] ? 10 : 0,
                'ticketsWithEffort' => 0,
                'budgetedHours' => $cid === $childIds[0] ? $budgetedHours : 0.0,
                'effortPoints' => 0.0,
                // aggregateByProgram sorts child rows by verdict rank.
                'verdict' => 'balanced',
            ];
        }

        $rows = $this->analyzer()->aggregateByProgram(
            $projectRows,
            [2 => $childIds],
            [2 => ['name' => 'Program 2']],
            $resources,
            $this->period(),
        );

        return $rows[2]; // keyed by programId
    }
}
