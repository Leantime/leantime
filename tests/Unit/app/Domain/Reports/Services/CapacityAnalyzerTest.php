<?php

namespace Tests\Unit\App\Domain\Reports\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Language;
use Leantime\Core\Resources\Models\PersonAllocation;
use Leantime\Core\Resources\Models\ResourceSummary;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Core\Support\DateTimeHelper;
use Leantime\Domain\Reports\Models\ReportPeriod;
use Leantime\Domain\Reports\Services\CapacityAnalyzer;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketsRepo;
use PHPUnit\Framework\MockObject\MockObject;
use Unit\TestCase;

class CapacityAnalyzerTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private TicketsRepo&MockObject $ticketsRepo;

    private CapacityAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();

        $environmentMock = $this->make(Environment::class, [
            'defaultTimezone' => 'UTC',
            'language' => 'en-US',
        ]);
        app()->instance(Environment::class, $environmentMock);

        $languageMock = $this->createMock(Language::class);
        $languageMock->method('__')->willReturnCallback(fn ($index) => [
            'language.dateformat' => 'm/d/Y',
            'language.timeformat' => 'h:i A',
        ][$index] ?? null);
        app()->instance(Language::class, $languageMock);

        CarbonImmutable::mixin(new CarbonMacros('UTC', 'en_US', 'm/d/Y', 'h:i A'));
        app()->instance(DateTimeHelper::class, new DateTimeHelper);
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-08 12:00:00', 'UTC'));

        $this->ticketsRepo = $this->createMock(TicketsRepo::class);
        $this->analyzer = new CapacityAnalyzer($this->ticketsRepo);

        // Default status vocabulary: 3=NEW, 4=INPROGRESS, 0=DONE, -1=DONE(archived),
        // 7 = a CUSTOM done status with a positive id.
        $this->ticketsRepo->method('getStateLabels')->willReturn([
            3 => ['statusType' => 'NEW', 'name' => 'New'],
            4 => ['statusType' => 'INPROGRESS', 'name' => 'In Progress'],
            0 => ['statusType' => 'DONE', 'name' => 'Done'],
            -1 => ['statusType' => 'DONE', 'name' => 'Archived'],
            7 => ['statusType' => 'DONE', 'name' => 'Shipped'],
        ]);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        app()->forgetInstance(DateTimeHelper::class);

        parent::tearDown();
    }

    /**
     * One-week period so availableHours equals the weekly allocation exactly.
     */
    private function oneWeekPeriod(): ReportPeriod
    {
        return ReportPeriod::fromRequest(['preset' => 'custom', 'from' => '06/01/2026', 'to' => '06/07/2026']);
    }

    /**
     * @param  array<int, array<string, mixed>>  $tickets
     */
    private function withTickets(array $tickets): void
    {
        $this->ticketsRepo->method('getAllByProjectId')->willReturn($tickets);
    }

    private function summaryWithWeeklyAllocation(int $projectId, float $weeklyHours, int $people = 1): ResourceSummary
    {
        $persons = [];
        for ($i = 0; $i < $people; $i++) {
            $persons[] = new PersonAllocation(
                itemId: $i + 1,
                userId: $i + 1,
                displayName: 'Person '.($i + 1),
                capacity: 40.0,
                allocations: [$projectId => $weeklyHours / $people],
            );
        }

        return new ResourceSummary([$projectId], $persons, [], [], 40.0 * $people, $weeklyHours, 0.0, 0.0);
    }

    public function test_custom_done_statuses_are_excluded_from_demand(): void
    {
        $this->withTickets([
            // Custom DONE status (positive id) — must NOT count as open demand.
            ['id' => 1, 'status' => 7, 'planHours' => 100.0, 'storypoints' => 0],
            // Default done + archived — excluded.
            ['id' => 2, 'status' => 0, 'planHours' => 50.0, 'storypoints' => 0],
            ['id' => 3, 'status' => -1, 'planHours' => 25.0, 'storypoints' => 0],
            // Open work — the only demand.
            ['id' => 4, 'status' => 3, 'planHours' => 10.0, 'storypoints' => 0],
        ]);

        $rows = $this->analyzer->analyzeProjects([10], $this->oneWeekPeriod(), $this->summaryWithWeeklyAllocation(10, 20.0), [10 => 'P']);

        $this->assertSame(1, $rows[10]['openTicketCount']);
        $this->assertEqualsWithDelta(10.0, $rows[10]['budgetedHours'], 0.001);
    }

    public function test_trust_signal_bands_drive_reference_demand(): void
    {
        // Low coverage (1 of 4 open tickets budgeted = 0.25 < 0.30) with effort present
        // -> trust 'effort', referenceDemand = storypoints × hoursPerPoint.
        $this->withTickets([
            ['id' => 1, 'status' => 3, 'planHours' => 8.0, 'storypoints' => 5],
            ['id' => 2, 'status' => 3, 'planHours' => 0, 'storypoints' => 5],
            ['id' => 3, 'status' => 3, 'planHours' => 0, 'storypoints' => 5],
            ['id' => 4, 'status' => 4, 'planHours' => 0, 'storypoints' => 5],
        ]);

        $rows = $this->analyzer->analyzeProjects([10], $this->oneWeekPeriod(), $this->summaryWithWeeklyAllocation(10, 20.0), [10 => 'P']);

        $this->assertSame('effort', $rows[10]['trustSignal']);
        $this->assertEqualsWithDelta(20 * CapacityAnalyzer::DEFAULT_HOURS_PER_POINT, $rows[10]['referenceDemand'], 0.001);
    }

    public function test_high_coverage_agreeing_estimates_trust_budgeted(): void
    {
        // Full coverage, divergence below threshold (40 budgeted vs 40 effort hours).
        $this->withTickets([
            ['id' => 1, 'status' => 3, 'planHours' => 20.0, 'storypoints' => 5],
            ['id' => 2, 'status' => 3, 'planHours' => 20.0, 'storypoints' => 5],
        ]);

        $rows = $this->analyzer->analyzeProjects([10], $this->oneWeekPeriod(), $this->summaryWithWeeklyAllocation(10, 20.0), [10 => 'P']);

        $this->assertSame('budgeted', $rows[10]['trustSignal']);
        $this->assertEqualsWithDelta(40.0, $rows[10]['referenceDemand'], 0.001);
    }

    public function test_diverging_estimates_flag_mixed_and_take_conservative_max(): void
    {
        // Full coverage but effort (10sp × 4h = 40h) vs budgeted (100h) diverge > 0.4 -> mixed, max() wins.
        $this->withTickets([
            ['id' => 1, 'status' => 3, 'planHours' => 50.0, 'storypoints' => 5],
            ['id' => 2, 'status' => 3, 'planHours' => 50.0, 'storypoints' => 5],
        ]);

        $rows = $this->analyzer->analyzeProjects([10], $this->oneWeekPeriod(), $this->summaryWithWeeklyAllocation(10, 20.0), [10 => 'P']);

        $this->assertSame('mixed', $rows[10]['trustSignal']);
        $this->assertEqualsWithDelta(100.0, $rows[10]['referenceDemand'], 0.001);
    }

    /**
     * @dataProvider verdictBoundaryProvider
     */
    public function test_verdict_boundaries(float $demandHours, float $weeklyAvailable, string $expectedVerdict): void
    {
        // Single fully-budgeted open ticket -> trust 'budgeted' -> referenceDemand = planHours.
        $this->withTickets([
            ['id' => 1, 'status' => 3, 'planHours' => $demandHours, 'storypoints' => 0],
        ]);

        $rows = $this->analyzer->analyzeProjects([10], $this->oneWeekPeriod(), $this->summaryWithWeeklyAllocation(10, $weeklyAvailable), [10 => 'P']);

        $this->assertSame($expectedVerdict, $rows[10]['verdict']);
    }

    public static function verdictBoundaryProvider(): array
    {
        return [
            'no capacity' => [100.0, 0.0, 'no_capacity'],
            'critical: gap ratio > 0.25' => [130.0, 100.0, 'critical'],
            'tight: 0 < ratio <= 0.25' => [110.0, 100.0, 'tight'],
            'balanced: -0.5 <= ratio <= 0' => [90.0, 100.0, 'balanced'],
            'buffer: ratio < -0.5' => [40.0, 100.0, 'buffer'],
        ];
    }

    public function test_projects_without_work_or_people_are_skipped_as_noise(): void
    {
        $this->withTickets([]);

        $rows = $this->analyzer->analyzeProjects([10], $this->oneWeekPeriod(), ResourceSummary::empty([10]), [10 => 'P']);

        $this->assertSame([], $rows);
    }

    // ─── Program rollup: supply is capacity, not booked hours ────────
    //
    // aggregateByProgram measures how much a program COULD do (capacity),
    // not how much is already booked against it. This keeps the rollup
    // consistent with the report's headline utilization tile, which reads
    // allocated/capacity — before this, a program with real headroom and
    // little booked reported no_capacity and could escalate as a false gap
    // on the strategy report. verdict()/trustSignal() themselves are
    // covered above; these pin the supply figure feeding them.

    public function test_program_supply_uses_capacity_not_allocated_hours(): void
    {
        // 40h capacity, only 5h booked against the program → supply is 40.
        $row = $this->rollup(
            [$this->personCap(1, capacity: 40.0, allocations: [7 => 5.0])],
            childIds: [7],
        );

        $this->assertEqualsWithDelta(40.0, $row['availableHours'], 0.001);
        $this->assertSame(1, $row['peopleCount']);
    }

    public function test_program_supply_counts_a_person_on_two_child_projects_once(): void
    {
        $row = $this->rollup(
            [$this->personCap(1, capacity: 40.0, allocations: [7 => 5.0, 8 => 5.0])],
            childIds: [7, 8],
        );

        $this->assertEqualsWithDelta(40.0, $row['availableHours'], 0.001);
        $this->assertSame(1, $row['peopleCount']);
    }

    public function test_program_supply_deducts_commitments_outside_the_program(): void
    {
        // 40h capacity, 10h here, 15h on a project outside this program →
        // 25h is what this program could still claim.
        $row = $this->rollup(
            [$this->personCap(1, capacity: 40.0, allocations: [7 => 10.0, 99 => 15.0])],
            childIds: [7],
        );

        $this->assertEqualsWithDelta(25.0, $row['availableHours'], 0.001);
    }

    public function test_outside_commitments_never_push_a_person_below_zero(): void
    {
        // Person 1 is over-committed elsewhere beyond their capacity: they
        // bring nothing here, but must not subtract from person 2.
        $row = $this->rollup(
            [
                $this->personCap(1, capacity: 40.0, allocations: [7 => 1.0, 99 => 100.0]),
                $this->personCap(2, capacity: 20.0, allocations: [7 => 5.0]),
            ],
            childIds: [7],
        );

        $this->assertEqualsWithDelta(20.0, $row['availableHours'], 0.001, 'clamped at 0, not -60');
    }

    public function test_people_not_allocated_to_the_program_contribute_nothing(): void
    {
        $row = $this->rollup(
            [$this->personCap(1, capacity: 40.0, allocations: [99 => 10.0])],
            childIds: [7],
        );

        $this->assertEqualsWithDelta(0.0, $row['availableHours'], 0.001);
        $this->assertSame(0, $row['peopleCount']);
    }

    /**
     * The regression this change exists for: a program with real headroom
     * and little booked used to read no_capacity because supply was measured
     * as hours-already-allocated. With capacity as supply it reads as buffer
     * — there IS room. (supply 40 vs demand 10 → ratio -0.75 → buffer.)
     */
    public function test_program_with_headroom_and_light_booking_reads_as_buffer(): void
    {
        $row = $this->rollup(
            [$this->personCap(1, capacity: 40.0, allocations: [7 => 0.5])],
            childIds: [7],
            budgetedHours: 10.0,
        );

        $this->assertNotSame('no_capacity', $row['verdict']);
        $this->assertSame('buffer', $row['verdict']);
    }

    /**
     * Work planned with nobody assigned stays no_capacity, distinct from
     * critical: it is an authoring gap ("assign people"), not a capacity
     * crisis, and painting it red trains readers to ignore red.
     */
    public function test_program_with_no_people_is_no_capacity_not_critical(): void
    {
        $row = $this->rollup([], childIds: [7], budgetedHours: 400.0);

        $this->assertSame('no_capacity', $row['verdict']);
    }

    private function personCap(int $itemId, float $capacity, array $allocations): PersonAllocation
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
     * Runs aggregateByProgram for one program over the given children,
     * feeding pre-built project rows so the ticket/demand side is fixed and
     * the assertions are about the capacity side. Demand sits on the first
     * child so totals are predictable regardless of child count.
     *
     * @param  array<int, PersonAllocation>  $people
     * @param  int[]  $childIds
     * @return array<string, mixed>
     */
    private function rollup(array $people, array $childIds, float $budgetedHours = 50.0): array
    {
        $projectRows = [];
        foreach ($childIds as $cid) {
            $isFirst = $cid === $childIds[0];
            $projectRows[$cid] = [
                'projectId' => $cid,
                'name' => 'Child '.$cid,
                'openTicketCount' => $isFirst ? 10 : 0,
                'ticketsWithBudget' => $isFirst ? 10 : 0,
                'ticketsWithEffort' => 0,
                'budgetedHours' => $isFirst ? $budgetedHours : 0.0,
                'effortPoints' => 0.0,
                'verdict' => 'balanced', // aggregateByProgram sorts children by verdict rank
            ];
        }

        $resources = new ResourceSummary([7, 8], $people, [], [], 0.0, 0.0, 0.0, 0.0);

        $rows = $this->analyzer->aggregateByProgram(
            $projectRows,
            [2 => $childIds],
            [2 => ['id' => 2, 'name' => 'Program 2']],
            $resources,
            $this->oneWeekPeriod(),
        );

        return $rows[2];
    }
}
