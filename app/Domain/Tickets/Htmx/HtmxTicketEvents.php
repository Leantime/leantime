<?php

namespace Leantime\Domain\Tickets\Htmx;

use Leantime\Core\Events\Htmx\HtmxEvent;
use Leantime\Core\Events\Htmx\InteractsWithHtmxEvents;

/**
 * Client (HTMX) data events for the Tickets domain.
 *
 * Naming follows the lt:{domain}:{entity}.{verb} convention. Legacy values ('ticket_update',
 * 'subtasks_update', 'subtasksUpdated') are dual-emitted via {@see \Leantime\Core\Events\Htmx\HtmxEvents}
 * during the migration window so existing listeners keep working.
 */
enum HtmxTicketEvents: string implements HtmxEvent
{
    use InteractsWithHtmxEvents;

    /** One or more tickets have been updated. */
    case UPDATE = 'lt:tickets:ticket.updated';

    /** A ticket's subtasks have changed. */
    case SUBTASK_UPDATE = 'lt:tickets:subtask.updated';
}
