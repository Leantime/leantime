<?php

namespace Leantime\Domain\WeeklyPlanning\Models;

/**
 * WeeklyPlan — one employee's plan for a single week.
 */
class WeeklyPlan
{
    public ?int $id = null;

    public ?int $employeeId = null;

    public ?int $teamLeadId = null;

    public ?string $month = null;

    public ?string $weekLabel = null;

    public ?string $weekStart = null;

    public ?string $weekEnd = null;

    public ?string $dateOfOneOnOne = null;

    /** draft | active | reviewed | closed */
    public string $status = 'draft';

    public ?string $topPriorities = null;

    public ?string $winsAndProgress = null;

    public ?string $challengesAndBlockers = null;

    public ?string $managerSupportNeeded = null;

    public ?string $ideasAndSuggestions = null;

    public ?string $growthCurrentFocus = null;

    public ?string $growthSupportNeeded = null;

    public ?string $growthNextMilestone = null;

    public ?string $nextWeekPriorities = null;

    public ?string $summary = null;

    public ?string $createdAt = null;

    public ?string $updatedAt = null;
}
