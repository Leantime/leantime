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
}
