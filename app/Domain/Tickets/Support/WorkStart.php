<?php

declare(strict_types=1);

namespace Leantime\Domain\Tickets\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Reads a to-do's Work Start (editFrom / editTo) as a plan.
 *
 * The one shared definition of the planning terms; nothing else keeps its own copy:
 *
 * - Any Work Start makes a to-do Planned for the day or days it covers.
 * - A Work Start with a real time is also Time-boxed.
 * - A date-only Work Start has no time: local midnight with no end (or an end equal to the
 *   start), or a span from one local midnight to a later one. A span's end day is excluded, the
 *   way calendars store all-day events.
 *
 * Dates are stored in UTC and read in the timezone passed in: the person the to-do is read for
 * (dtHelper() in a request; their stored timezone setting in a feed or a scheduled job).
 *
 * This class only reads dates. Leaving out done to-dos, and "due wins over planned", are the
 * caller's rules.
 */
final class WorkStart
{
    /** The most days a single Work Start is read as covering. Guards against bad data. */
    public const MAX_DAYS = 366;

    /**
     * Whether the Work Start has no time of day (a plan for whole days).
     *
     * @param  string|null  $editFrom  Work Start as stored (UTC).
     * @param  string|null  $editTo  Work End as stored (UTC), if any.
     * @param  string  $timezone  The person's timezone, e.g. "America/Los_Angeles".
     */
    public static function isDateOnly(?string $editFrom, ?string $editTo, string $timezone): bool
    {
        $start = self::local($editFrom, $timezone);
        if ($start === null || ! self::isMidnight($start)) {
            return false;
        }

        $end = self::local($editTo, $timezone);
        if ($end === null || $end->equalTo($start)) {
            return true;
        }

        return $end->greaterThan($start) && self::isMidnight($end);
    }

    /**
     * Whether the Work Start has a real time (a time box). A to-do that is time-boxed is
     * always Planned too.
     *
     * @param  string|null  $editFrom  Work Start as stored (UTC).
     * @param  string|null  $editTo  Work End as stored (UTC), if any.
     * @param  string  $timezone  The person's timezone.
     */
    public static function isTimeBoxed(?string $editFrom, ?string $editTo, string $timezone): bool
    {
        return self::local($editFrom, $timezone) !== null
            && ! self::isDateOnly($editFrom, $editTo, $timezone);
    }

    /**
     * The local days (YYYY-MM-DD) the Work Start plans the to-do for, earliest first.
     *
     * A Work Start without an end, or with an end at or before the start, covers its start day.
     * Otherwise it covers every day up to the instant before the end, so a span ending at a
     * midnight leaves that day out.
     *
     * @param  string|null  $editFrom  Work Start as stored (UTC).
     * @param  string|null  $editTo  Work End as stored (UTC), if any.
     * @param  string  $timezone  The person's timezone.
     * @return list<string> Empty when there is no valid Work Start.
     */
    public static function plannedDays(?string $editFrom, ?string $editTo, string $timezone): array
    {
        $start = self::local($editFrom, $timezone);
        if ($start === null) {
            return [];
        }

        $end = self::local($editTo, $timezone);
        $lastDay = ($end !== null && $end->greaterThan($start))
            ? $end->subSecond()->startOfDay()
            : $start->startOfDay();

        // Re-take the start of each day after stepping: where daylight saving skips midnight, a
        // day starts at 01:00 and stepping a whole day would carry that hour into later days.
        $days = [];
        for ($day = $start->startOfDay(); $day->lessThanOrEqualTo($lastDay) && count($days) < self::MAX_DAYS; $day = $day->addDay()->startOfDay()) {
            $days[] = $day->format('Y-m-d');
        }

        return $days;
    }

    /**
     * Whether the Work Start makes the to-do Planned for a given day.
     *
     * @param  string|null  $editFrom  Work Start as stored (UTC).
     * @param  string|null  $editTo  Work End as stored (UTC), if any.
     * @param  CarbonInterface|string  $day  The day: YYYY-MM-DD in the person's timezone, or a date (read in that timezone).
     * @param  string  $timezone  The person's timezone.
     */
    public static function isPlannedForDay(?string $editFrom, ?string $editTo, CarbonInterface|string $day, string $timezone): bool
    {
        // An immutable copy, so a mutable Carbon passed in keeps its own timezone.
        $dayString = $day instanceof CarbonInterface
            ? CarbonImmutable::instance($day)->setTimezone($timezone)->format('Y-m-d')
            : $day;

        return in_array($dayString, self::plannedDays($editFrom, $editTo, $timezone), true);
    }

    /**
     * The start of a local day: what a date-only Work Start stores (converted to UTC on save).
     *
     * @param  string  $day  YYYY-MM-DD in the person's timezone.
     * @param  string  $timezone  The person's timezone.
     * @return CarbonImmutable|null Local midnight of that day, or null when $day isn't a real YYYY-MM-DD day.
     */
    public static function startOfLocalDay(string $day, string $timezone): ?CarbonImmutable
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $day) !== 1) {
            return null;
        }

        try {
            $start = CarbonImmutable::createFromFormat('!Y-m-d', $day, $timezone);
        } catch (\Throwable) {
            return null;
        }

        // createFromFormat rolls over impossible dates (2026-02-30 becomes March 2); refuse them.
        if ($start === null || $start->format('Y-m-d') !== $day) {
            return null;
        }

        return $start;
    }

    /**
     * A stored date read in the person's timezone, or null when it's empty or a zero-date sentinel.
     */
    private static function local(?string $stored, string $timezone): ?CarbonImmutable
    {
        if ($stored === null || ! dtHelper()->isValidDateString($stored)) {
            return null;
        }

        try {
            return dtHelper()->parseDbDateTime($stored)->setTimezone($timezone);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Whether a moment is the start of its local day. That's usually 00:00, but 01:00 on a day
     * where daylight saving skips midnight (for example America/Santiago in September).
     */
    private static function isMidnight(CarbonImmutable $moment): bool
    {
        return $moment->equalTo($moment->startOfDay());
    }
}
