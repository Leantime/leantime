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
        // Nothing to tell anyone; decided here so the notifier and its repositories aren't even built.
        if ($event->assignedToSelf) {
            return;
        }

        try {
            app()->make(AssignmentNotifier::class)->notify($event);
        } catch (\Throwable $e) {
            // Class and code only: a QueryException's message carries the bound SQL values,
            // i.e. the actor's name and the to-do's headline.
            Log::warning('Assignment notification failed for to-do #'.$event->ticketId.' ('.get_class($e).($e->getCode() ? ' '.$e->getCode() : '').')');
        }
    }
}
