<?php

namespace Leantime\Domain\Calendar\Events;

use Leantime\Core\Events\Concerns\InteractsWithFilters;
use Leantime\Core\Events\Contracts\LeantimeFilter;

/**
 * Filters the plugin sections shown in the calendar settings modal.
 *
 * Section array structure:
 *   - id: string (unique identifier)
 *   - icon: string (FontAwesome class or SVG markup)
 *   - iconType: string ('fontawesome'|'svg', default 'fontawesome')
 *   - title: string (section heading)
 *   - description: string (optional description text)
 *   - content: string (raw HTML content - plugin must sanitize)
 *   - actions: array (optional action buttons with url, label, class, icon, type keys)
 */
final class CalendarSettingsSectionsFilter implements LeantimeFilter
{
    use InteractsWithFilters;

    /**
     * @param  array  $sections  The sections to filter.
     */
    public function __construct(
        public array $sections = [],
    ) {}

    /**
     * The sections threaded through the pipeline.
     */
    public function payload(): mixed
    {
        return $this->sections;
    }
}
