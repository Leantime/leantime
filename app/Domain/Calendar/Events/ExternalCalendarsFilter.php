<?php

namespace Leantime\Domain\Calendar\Events;

use Leantime\Core\Events\Concerns\InteractsWithFilters;
use Leantime\Core\Events\Contracts\LeantimeFilter;

/**
 * Filters the user's external calendars before they are listed on the calendar page and in the
 * calendar settings modal. Plugins add the calendars they manage here.
 *
 * Each entry is an array with id, name, url and colorClass. A plugin entry can also set:
 *   - managedByPlugin: bool (hides the core edit/delete controls)
 *   - subtitle: string
 *   - actions: array of [url, icon, tooltip, type] (shown in the settings modal only)
 */
final class ExternalCalendarsFilter implements LeantimeFilter
{
    use InteractsWithFilters;

    /**
     * @param  array  $calendars  The external calendars to filter.
     * @param  bool  $inSettings  True in the calendar settings modal, where entries may carry actions.
     */
    public function __construct(
        public array $calendars,
        public readonly bool $inSettings = false,
    ) {}

    /**
     * The calendars threaded through the pipeline.
     */
    public function payload(): mixed
    {
        return $this->calendars;
    }
}
