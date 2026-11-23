<?php

namespace Leantime\Domain\Reminders;

use Illuminate\Console\Scheduling\Schedule;
use Leantime\Core\Events\EventDispatcher;

// Morning summaries. Every five minutes: each run sends the summaries that have come due, so a
// missed run catches up. Never two runs at once, and on one server only where the cache store can
// share locks between servers: "was today's summary sent?" is a check of the inbox followed by a
// write, which only holds while one run does it at a time. The overlap lock expires after 10
// minutes, so a run that crashed holding it delays summaries by at most that long.
EventDispatcher::add_event_listener('leantime.core.console.consolekernel.schedule.cron', function ($params) {
    if (get_class($scheduler = $params['schedule']) !== Schedule::class) {
        return;
    }

    $scheduler
        // Returns nothing: schedule:test reads a returned 2 or 3 as "failed" / "skipped".
        ->call(function (): void {
            app()->make(Services\ReminderDispatcher::class)->run();
        })
        ->everyFiveMinutes()
        ->name('reminders:dispatch')
        ->withoutOverlapping(10)
        ->onOneServer();
});
