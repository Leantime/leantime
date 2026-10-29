<?php

namespace Leantime\Domain\Notifications\Listeners;

use Illuminate\Support\Facades\Log;
use Leantime\Domain\Notifications\Services\AssignmentNotifier;
use Leantime\Domain\Tickets\Events\TicketAssigned;

/**
 * Tells the new assignee of a to-do (see AssignmentNotifier). Runs after the
 * to-do was written, so a failure here is logged and never fails the save.
 */
class NotifyAssignee
{
    public function handle(TicketAssigned $event): void
    {
        try {
            app()->make(AssignmentNotifier::class)->notify($event);
        } catch (\Throwable $e) {
            Log::warning('Assignment notification failed for to-do #'.$event->ticketId.': '.$e->getMessage());
        }
    }
}
