<?php

namespace Leantime\Domain\Calendar\Events;

use Leantime\Core\Events\Concerns\InteractsWithFilters;
use Leantime\Core\Events\Contracts\LeantimeFilter;

/**
 * Filters the calendar providers offered in the "Connect Calendar" modal, below the core iCal import.
 *
 * Provider array structure:
 *   - id: string (unique identifier)
 *   - icon: string (FontAwesome class or SVG markup)
 *   - iconType: string ('fontawesome'|'svg', default 'fontawesome')
 *   - title: string (display name)
 *   - description: string (short description)
 *   - actionUrl: string (URL to connect)
 *   - actionLabel: string (button text)
 *   - actionType: string ('link'|'modal', default 'link')
 */
final class CalendarProvidersFilter implements LeantimeFilter
{
    use InteractsWithFilters;

    /**
     * @param  array  $providers  The providers to filter.
     */
    public function __construct(
        public array $providers = [],
    ) {}

    /**
     * The providers threaded through the pipeline.
     */
    public function payload(): mixed
    {
        return $this->providers;
    }
}
