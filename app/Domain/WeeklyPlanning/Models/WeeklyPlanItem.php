<?php

namespace Leantime\Domain\WeeklyPlanning\Models;

/**
 * WeeklyPlanItem — links a ticket/task to a weekly plan with completion tracking.
 */
class WeeklyPlanItem
{
    public ?int $id = null;

    public ?int $weeklyPlanId = null;

    public ?int $ticketId = null;

    public ?string $taskTitle = null;

    public int $priority = 0;

    public ?string $expectedOutcome = null;

    /** not_started | in_progress | blocked | completed | not_completed */
    public string $status = 'not_started';

    public ?string $completionReason = null;

    public ?string $supportNeeded = null;

    public ?string $newDueDate = null;
}
