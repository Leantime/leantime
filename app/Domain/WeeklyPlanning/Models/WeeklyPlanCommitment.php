<?php

namespace Leantime\Domain\WeeklyPlanning\Models;

/**
 * WeeklyPlanCommitment — a follow-up task with owner and deadline.
 */
class WeeklyPlanCommitment
{
    public ?int $id = null;

    public ?int $weeklyPlanId = null;

    public ?string $task = null;

    public ?int $ownerId = null;

    public ?string $deadline = null;

    /** pending | done */
    public string $status = 'pending';

    public ?string $createdAt = null;
}
