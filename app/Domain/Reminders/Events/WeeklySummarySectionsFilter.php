<?php

namespace Leantime\Domain\Reminders\Events;

use Leantime\Core\Events\Concerns\InteractsWithFilters;
use Leantime\Core\Events\Contracts\LeantimeFilter;

/**
 * Lets plugins add sections to one person's weekly summary. The same sections go to the email,
 * the push (titles only), the inbox and the API. Plugins never send anything themselves.
 *
 * It runs from the scheduler, without a session: read the person from $userId, never from the
 * session. Dates are the person's local days ('Y-m-d').
 *
 * A section:
 *   - kind: 'plugin:{plugin}.{section}' (letters, digits, - and _), stable across releases
 *   - title: string, the section heading
 *   - lines: up to 5 short plain-text lines (HTML is removed)
 *   - url: optional link, a path ('/my-week') or a full URL on this Leantime
 *   - countsForSending: optional, default false. Only a section with true makes a summary go out
 *     when nothing else would; without it a section only adds to a summary that's going out.
 *
 * Sections that don't follow this shape are left out.
 */
final class WeeklySummarySectionsFilter implements LeantimeFilter
{
    use InteractsWithFilters;

    /**
     * @param  array<int, array<string, mixed>>  $sections  The sections so far.
     * @param  int  $userId  Whose summary it is.
     * @param  string  $weekStart  The first local day the summary covers ('Y-m-d').
     * @param  string  $weekEnd  The last local day it covers ('Y-m-d').
     * @param  string  $timezone  The person's timezone.
     */
    public function __construct(
        public array $sections = [],
        public int $userId = 0,
        public string $weekStart = '',
        public string $weekEnd = '',
        public string $timezone = 'UTC',
    ) {}

    /**
     * The sections threaded through the pipeline.
     */
    public function payload(): mixed
    {
        return $this->sections;
    }
}
