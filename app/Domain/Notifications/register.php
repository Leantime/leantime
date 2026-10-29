<?php

use Illuminate\Console\Scheduling\Schedule;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Notifications\Listeners\NotifyAssignee;
use Leantime\Domain\Notifications\Listeners\NotifyProjectUsers;
use Leantime\Domain\Notifications\Services\WebhookQueue;
use Leantime\Domain\Tickets\Events\TicketAssigned;

EventDispatcher::add_event_listener('leantime.domain.projects.services.projects.notifyProjectUsers.notifyProjectUsers', NotifyProjectUsers::class);

// "Assigned to you": the one person a to-do was assigned to, on every assignment from the web or the app.
EventDispatcher::add_event_listener(TicketAssigned::class, NotifyAssignee::class);

// Personal webhooks drain their own queue channel every minute, independent of the DEFAULT queue.
// The overlap lock expires after 10 minutes, so a run that crashed holding it stalls deliveries
// for at most that long instead of Laravel's default 24 hours.
EventDispatcher::add_event_listener('leantime.core.console.consolekernel.schedule.cron', function ($params) {

    if (get_class($scheduler = $params['schedule']) !== Schedule::class) {
        return;
    }

    $scheduler
        ->call(fn () => app()->make(WebhookQueue::class)->processQueue())
        ->everyMinute()
        ->name('queue:webhooks')
        ->withoutOverlapping(10);
});
