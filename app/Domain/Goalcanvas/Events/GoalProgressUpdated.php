<?php

namespace Leantime\Domain\Goalcanvas\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a goal's current value changed.
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class GoalProgressUpdated implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $goalId  The goal (canvas item) id.
     * @param  int|null  $projectId  The goal's project.
     * @param  float|null  $previousValue  The current value before the write (null when unset).
     * @param  float|null  $currentValue  The current value after the write (null when unset).
     * @param  float|null  $endValue  The goal's target value (null when unset).
     * @param  bool  $targetReached  True when this update crossed the target: reached now (>= endValue, or <= endValue for a decreasing goal whose endValue is below its startValue) and not reached before.
     */
    public function __construct(
        public readonly int $goalId,
        public readonly ?int $projectId,
        public readonly ?float $previousValue,
        public readonly ?float $currentValue,
        public readonly ?float $endValue,
        public readonly bool $targetReached,
    ) {}
}
