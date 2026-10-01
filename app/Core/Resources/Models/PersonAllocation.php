<?php

declare(strict_types=1);

namespace Leantime\Core\Resources\Models;

/**
 * A person allocated across one or more projects.
 *
 * `allocations` is a per-project map: `[projectId => weeklyHours]`. The
 * provider is responsible for keeping keys and hours in sync with its own
 * canvas storage. Consumers only read.
 */
final class PersonAllocation
{
    /**
     * @param  array<int, float>  $allocations  projectId => weekly hours
     */
    public function __construct(
        public readonly int $itemId,
        public readonly ?int $userId,
        public readonly string $displayName,
        public readonly float $capacity,
        public readonly array $allocations,
    ) {}

    /**
     * Weekly hours allocated across all projects.
     */
    public function totalAllocated(): float
    {
        return array_sum($this->allocations);
    }

    /**
     * Remaining weekly capacity (never negative). Over-allocation is a real
     * product state — callers that want to render an "over-allocated" badge
     * compare `totalAllocated()` against `capacity` directly.
     */
    public function available(): float
    {
        return max(0.0, $this->capacity - $this->totalAllocated());
    }
}
