<?php

declare(strict_types=1);

namespace Leantime\Domain\Users\Contracts;

/**
 * Employment type for a user. Drives how weekly_hours is interpreted
 * (target vs ceiling vs bill-to cap vs best-effort) and how downstream
 * capacity math (over-allocation warnings, portfolio rollups) treats
 * this person.
 *
 * Stored on zp_user.employment_type (nullable — admin sets it explicitly,
 * no assumed default; treat NULL as "not configured").
 *
 * Value semantics:
 *   FTE        — weekly_hours is the *target*. Over = amber "over target"
 *                (burnout signal). Common case.
 *   PT         — weekly_hours is a *ceiling* the user set. Over = red
 *                "over ceiling" (planning violates commitment).
 *   Contractor — weekly_hours is a *billable cap*. Over = red "over
 *                billable cap" (cost overrun risk).
 *   Volunteer  — weekly_hours is *best-effort*. Excluded from over-
 *                allocation math entirely; contributes to project totals
 *                as separate "volunteer support" throughput.
 */
enum EmploymentType: string
{
    case FTE = 'fte';
    case PartTime = 'pt';
    case Contractor = 'contractor';
    case Volunteer = 'volunteer';

    /**
     * Human-readable label — used in form selects and badges. Not
     * translated at the enum level; templates run these through __()
     * with the corresponding language key when i18n is needed.
     */
    public function label(): string
    {
        return match ($this) {
            self::FTE => 'Full-time',
            self::PartTime => 'Part-time',
            self::Contractor => 'Contractor',
            self::Volunteer => 'Volunteer',
        };
    }

    /**
     * i18n key for the label — used by admin templates and any
     * downstream UI that wants localised strings.
     */
    public function langKey(): string
    {
        return 'users.employment_type.'.$this->value;
    }

    /**
     * Whether over-allocation warnings apply to this person. False for
     * Volunteer only — a volunteer at 3× their nominal hours is not a
     * problem, it's a gift. Everyone else gets warned.
     */
    public function countsAgainstCapacity(): bool
    {
        return $this !== self::Volunteer;
    }

    /**
     * The tone of the over-cap warning when it fires. FTE = amber
     * (burnout signal, not a violation). PT/Contractor = red
     * (violates an explicit ceiling or a billable cap).
     */
    public function overCapTone(): string
    {
        return match ($this) {
            self::FTE => 'warn',
            self::PartTime, self::Contractor => 'danger',
            self::Volunteer => 'none',
        };
    }
}
