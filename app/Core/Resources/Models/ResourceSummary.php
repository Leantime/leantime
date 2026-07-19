<?php

declare(strict_types=1);

namespace Leantime\Core\Resources\Models;

/**
 * The shape of a Resources aggregation, as seen by every consumer (report
 * engine, UI section, JSON-RPC caller).
 *
 * Providers assemble this by reading their own storage and mapping into these
 * value objects. Consumers depend only on this class — never on the provider's
 * internal repository or canvas tables.
 *
 * Empty state ({@see self::empty()}) is a valid return, not an error. A
 * consumer with no gateway registered gets null from the registry; a consumer
 * with a gateway that has nothing authored gets an empty summary. Both branches
 * are legitimate design states — the caller decides which affordance to
 * render.
 */
final class ResourceSummary
{
    /**
     * @param  int[]  $projectIds  The projects this summary aggregates over.
     * @param  array<int, PersonAllocation>  $people
     * @param  array<int, BudgetLine>  $budget
     * @param  array<int, Dependency>  $dependencies
     * @param  float  $totalCapacity  Sum of `people[].capacity`.
     * @param  float  $totalAllocated  Sum of allocations across people.
     * @param  float  $totalBudgeted  Sum of `budget[].budgeted`.
     * @param  float  $totalSpent  Sum of `budget[].spent`.
     */
    public function __construct(
        public readonly array $projectIds,
        public readonly array $people,
        public readonly array $budget,
        public readonly array $dependencies,
        public readonly float $totalCapacity,
        public readonly float $totalAllocated,
        public readonly float $totalBudgeted,
        public readonly float $totalSpent,
    ) {}

    /**
     * Empty summary — no resources authored across the given project set.
     * Consumers should treat this as "the plugin is installed and answered,
     * but there's nothing to show." Distinct from a null registry lookup.
     *
     * @param  int[]  $projectIds
     */
    public static function empty(array $projectIds = []): self
    {
        return new self($projectIds, [], [], [], 0.0, 0.0, 0.0, 0.0);
    }

    /**
     * True when the summary has no people, no budget, and no dependencies.
     */
    public function isEmpty(): bool
    {
        return $this->people === [] && $this->budget === [] && $this->dependencies === [];
    }

    /**
     * Capacity utilization as a 0-100 percentage. Returns 0 when no capacity
     * has been declared to avoid divide-by-zero in report tiles.
     */
    public function capacityUtilization(): float
    {
        if ($this->totalCapacity <= 0) {
            return 0.0;
        }

        return round(($this->totalAllocated / $this->totalCapacity) * 100, 1);
    }

    /**
     * Budget utilization as a 0-100 percentage. Returns 0 when no budget has
     * been declared.
     */
    public function budgetUtilization(): float
    {
        if ($this->totalBudgeted <= 0) {
            return 0.0;
        }

        return round(($this->totalSpent / $this->totalBudgeted) * 100, 1);
    }
}
