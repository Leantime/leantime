<?php

namespace Unit\app\Domain\Reminders;

use Leantime\Domain\Reminders\Services\SummaryBuilder;

/**
 * The builder with its lookups that need the rest of Leantime replaced: status 0 and -1 are done,
 * company role 30+ or project role 30 is a manager, and listed projects are hidden.
 */
class TestSummaryBuilder extends SummaryBuilder
{
    /** @var array<int, int> */
    public array $hiddenProjects = [];

    protected function isDone(int $projectId, mixed $status): bool
    {
        return in_array((int) $status, [0, -1], true);
    }

    protected function canSee(int $userId, int $projectId): bool
    {
        return ! in_array($projectId, $this->hiddenProjects, true);
    }

    protected function isManager(string $role, string $projectRole): bool
    {
        return (int) $role >= 30 || $projectRole === '30';
    }
}
