<?php

namespace Leantime\Domain\WeeklyPlanning\Models;

/**
 * WeeklyPlanFeedback — one of 4 directional feedback entries per plan.
 */
class WeeklyPlanFeedback
{
    public ?int $id = null;

    public ?int $weeklyPlanId = null;

    public ?int $fromUserId = null;

    public ?int $toUserId = null;

    /**
     * manager_to_employee_working | manager_to_employee_improve |
     * employee_to_manager_helping | employee_to_manager_improve
     */
    public string $type = 'manager_to_employee_working';

    public ?string $message = null;

    public ?string $createdAt = null;
}
