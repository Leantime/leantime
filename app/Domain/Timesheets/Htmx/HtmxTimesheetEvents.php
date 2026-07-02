<?php

namespace Leantime\Domain\Timesheets\Htmx;

use Leantime\Core\Events\Htmx\HtmxEvent;
use Leantime\Core\Events\Htmx\InteractsWithHtmxEvents;

/**
 * Client (HTMX) data events for the Timesheets domain.
 *
 * Naming follows the lt:{domain}:{entity}.{verb} convention used across domains
 * (see Tickets\Htmx\HtmxTicketEvents).
 */
enum HtmxTimesheetEvents: string implements HtmxEvent
{
    use InteractsWithHtmxEvents;

    /** A timesheet entry's logged duration has changed. */
    case ENTRY_UPDATED = 'lt:timesheets:entry.updated';
}
