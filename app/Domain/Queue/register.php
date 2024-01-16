<?php

namespace Leantime\Domain\Queue;

use Leantime\Core\Events;
use Illuminate\Console\Scheduling\Schedule;
use Leantime\Domain\Queue\Workers\Workers;

Events::add_event_listener('leantime.core.consolekernel.schedule.cron', function ($params) {
    /** @var Schedule $scheduler */
    if (get_class($scheduler = $params['schedule']) !== Schedule::class) {
        return;
    }

    $scheduler
        ->call(fn () => app()->make(Services\Queue::class)->processQueue(Workers::EMAILS))
        ->everyMinute();

    $scheduler
        ->call(fn () => app()->make(Services\Queue::class)->processQueue(Workers::HTTPREQUESTS))
        ->everyFiveMinutes();
});
