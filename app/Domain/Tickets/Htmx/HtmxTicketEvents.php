<?php

namespace Leantime\Domain\Tickets\Htmx;

enum HtmxTicketEvents: string
{
    /**
     * Event to be sent when one or more tickets have been updated
     */
    case UPDATE = 'ticket_update';

    case SUBTASK_UPDATE = 'subtasks_update';

}
