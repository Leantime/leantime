<?php

namespace Leantime\Domain\Notifications\Services;

use Carbon\CarbonInterface;

/**
 * Dates and times as people say them in notifications: "Fri, Nov 28" rather than "11/28/2026",
 * and "Yesterday 4:02 PM" or "Saturday 9:00 PM" rather than a full timestamp.
 *
 * Both inputs must already be in the reader's timezone; this never converts.
 */
final class FriendlyDate
{
    /**
     * A day: "Fri, Nov 28", with the year when it isn't the current year ("Fri, Jan 9, 2027").
     */
    public static function day(CarbonInterface $date, CarbonInterface $now, string $locale = 'en'): string
    {
        $localized = $date->locale(self::carbonLocale($locale));

        return $date->year === $now->year
            ? $localized->isoFormat('ddd, MMM D')
            : $localized->isoFormat('ddd, MMM D, YYYY');
    }

    /**
     * A moment relative to now: "4:02 PM" today, "Yesterday 4:02 PM", the weekday within the
     * last week ("Saturday 9:00 PM"), else the day and time ("Fri, Nov 14, 4:02 PM").
     *
     * @param  string  $yesterdayPattern  The translated "Yesterday %s" pattern; %s is the time.
     */
    public static function when(CarbonInterface $at, CarbonInterface $now, string $locale, string $yesterdayPattern): string
    {
        $time = $at->format('g:i A');
        $days = (int) $at->copy()->startOfDay()->diffInDays($now->copy()->startOfDay(), false);

        if ($days <= 0) {
            return $time;
        }
        if ($days === 1) {
            return sprintf($yesterdayPattern, $time);
        }
        if ($days < 7) {
            return $at->locale(self::carbonLocale($locale))->isoFormat('dddd').' '.$time;
        }

        return self::day($at, $now, $locale).', '.$time;
    }

    /** "en-US" → "en_US", the form Carbon's translations use. */
    private static function carbonLocale(string $locale): string
    {
        return str_replace('-', '_', $locale !== '' ? $locale : 'en');
    }
}
