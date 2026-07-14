<?php

declare(strict_types=1);

namespace Unit\app\Core\Resources\Models;

use Leantime\Core\Resources\Models\BudgetLine;
use Leantime\Core\Resources\Models\Dependency;
use Leantime\Core\Resources\Models\PersonAllocation;
use Leantime\Core\Resources\Models\ResourceSummary;
use Unit\TestCase;

/**
 * ResourceSummary value-object arithmetic tests. Utilization math is
 * consumed directly by the report tiles, so its edge cases (zero-divide,
 * empty aggregation) need to be locked in.
 */
class ResourceSummaryTest extends TestCase
{
    public function test_empty_returns_a_summary_with_zero_totals_and_marks_is_empty(): void
    {
        $summary = ResourceSummary::empty([1, 2, 3]);

        $this->assertSame([1, 2, 3], $summary->projectIds);
        $this->assertSame([], $summary->people);
        $this->assertSame([], $summary->budget);
        $this->assertSame([], $summary->dependencies);
        $this->assertSame(0.0, $summary->totalCapacity);
        $this->assertSame(0.0, $summary->totalAllocated);
        $this->assertTrue($summary->isEmpty());
    }

    public function test_capacity_utilization_is_zero_when_no_capacity_declared(): void
    {
        $summary = ResourceSummary::empty([1]);

        // Divide-by-zero must not occur — the report tile calls this
        // unconditionally.
        $this->assertSame(0.0, $summary->capacityUtilization());
    }

    public function test_capacity_utilization_returns_percent_when_capacity_declared(): void
    {
        $summary = new ResourceSummary(
            projectIds: [1],
            people: [$this->makePerson(40, [1 => 30])],
            budget: [],
            dependencies: [],
            totalCapacity: 40.0,
            totalAllocated: 30.0,
            totalBudgeted: 0.0,
            totalSpent: 0.0,
        );

        $this->assertSame(75.0, $summary->capacityUtilization());
    }

    public function test_budget_utilization_edge_cases(): void
    {
        $noBudget = ResourceSummary::empty([1]);
        $this->assertSame(0.0, $noBudget->budgetUtilization());

        $withBudget = new ResourceSummary(
            projectIds: [1],
            people: [],
            budget: [$this->makeBudget(1000.0, 250.0)],
            dependencies: [],
            totalCapacity: 0.0,
            totalAllocated: 0.0,
            totalBudgeted: 1000.0,
            totalSpent: 250.0,
        );
        $this->assertSame(25.0, $withBudget->budgetUtilization());
    }

    public function test_person_allocation_totals_and_availability(): void
    {
        $person = $this->makePerson(40, [1 => 20, 2 => 15]);

        $this->assertSame(35.0, $person->totalAllocated());
        $this->assertSame(5.0, $person->available());
    }

    public function test_person_over_allocation_reports_zero_available(): void
    {
        // available() clamps at 0; over-allocation is a real product state
        // callers detect by comparing totalAllocated() > capacity directly.
        $person = $this->makePerson(40, [1 => 45]);

        $this->assertSame(45.0, $person->totalAllocated());
        $this->assertSame(0.0, $person->available());
        $this->assertGreaterThan($person->capacity, $person->totalAllocated());
    }

    public function test_is_empty_true_only_when_all_three_sections_empty(): void
    {
        $onlyPeople = new ResourceSummary(
            projectIds: [1],
            people: [$this->makePerson(40, [])],
            budget: [],
            dependencies: [],
            totalCapacity: 40.0,
            totalAllocated: 0.0,
            totalBudgeted: 0.0,
            totalSpent: 0.0,
        );

        $this->assertFalse($onlyPeople->isEmpty());
    }

    public function test_is_empty_false_when_only_dependencies_present(): void
    {
        // A partnership-heavy program can have zero people and zero budget
        // authored but still be non-empty; the section must render.
        $onlyDeps = new ResourceSummary(
            projectIds: [1],
            people: [],
            budget: [],
            dependencies: [$this->makeDependency()],
            totalCapacity: 0.0,
            totalAllocated: 0.0,
            totalBudgeted: 0.0,
            totalSpent: 0.0,
        );

        $this->assertFalse($onlyDeps->isEmpty());
    }

    /**
     * @param  array<int, float>  $allocations
     */
    private function makePerson(float $capacity, array $allocations): PersonAllocation
    {
        return new PersonAllocation(
            itemId: 1,
            userId: null,
            displayName: 'Test',
            capacity: $capacity,
            allocations: $allocations,
        );
    }

    private function makeBudget(float $budgeted, float $spent): BudgetLine
    {
        return new BudgetLine(
            itemId: 1,
            projectId: 1,
            label: 'Test',
            budgeted: $budgeted,
            spent: $spent,
            color: null,
        );
    }

    private function makeDependency(): Dependency
    {
        return new Dependency(
            itemId: 1,
            partnerName: 'Test',
            type: 'partner',
            confirmed: false,
        );
    }
}
