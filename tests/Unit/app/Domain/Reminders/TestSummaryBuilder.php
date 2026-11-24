<?php

namespace Unit\app\Domain\Reminders;

use Leantime\Domain\Reminders\Services\SummaryBuilder;

/**
 * The builder with its lookups that need the rest of Leantime replaced: status 0 and -1 are done,
 * company role 30+ or project role 30 is a manager, listed projects are hidden, and listed
 * "userId|projectId|permission" combinations are denied.
 */
class TestSummaryBuilder extends SummaryBuilder
{
    /** @var array<int, int> */
    public array $hiddenProjects = [];

    /** @var array<int, string> "userId|projectId|permission" the person's role lacks */
    public array $deniedPermissions = [];

    protected function isDone(int $projectId, mixed $status): bool
    {
        return in_array((int) $status, [0, -1], true);
    }

    protected function canSee(int $userId, int $projectId): bool
    {
        return ! in_array($projectId, $this->hiddenProjects, true);
    }

    protected function mayView(int $userId, int $projectId, string $permission): bool
    {
        return ! in_array($userId.'|'.$projectId.'|'.$permission, $this->deniedPermissions, true);
    }

    protected function isManager(string $role, string $projectRole): bool
    {
        return (int) $role >= 30 || $projectRole === '30';
    }
}
