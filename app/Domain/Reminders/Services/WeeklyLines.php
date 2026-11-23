<?php

namespace Leantime\Domain\Reminders\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Language as LanguageCore;

/**
 * The weekly summary's short lines, worded the same in the email, the push and the web panel.
 */
final class WeeklyLines
{
    /** A weekday's name from the language file ("Monday", or "Mon" when short), by ISO day. */
    public static function dayName(LanguageCore $language, int $isoDay, bool $short): string
    {
        $names = explode(',', (string) $language->__($short ? 'language.dayNamesShort' : 'language.dayNames'));

        return trim($names[$isoDay % 7] ?? (string) $isoDay);
    }

    /**
     * "Wed: 2 planned (1 with a set time)".
     *
     * @param  array{date: string, count: int, timeBoxed: int}  $day
     */
    public static function planned(LanguageCore $language, array $day): string
    {
        $line = sprintf($language->__('reminders.weekly_planned_day'), self::dayName($language, CarbonImmutable::parse($day['date'])->dayOfWeekIso, true), $day['count']);

        return $day['timeBoxed'] > 0 ? $line.' ('.sprintf($language->__('reminders.weekly_planned_time'), $day['timeBoxed']).')' : $line;
    }

    /** "You finished 6 to-dos last week." */
    public static function finished(LanguageCore $language, int $count): string
    {
        return $count === 1 ? $language->__('reminders.weekly_finished_one') : sprintf($language->__('reminders.weekly_finished'), $count);
    }
}
