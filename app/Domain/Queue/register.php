<?php

namespace Leantime\Domain\Queue;

use Illuminate\Console\Scheduling\Schedule;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Queue\Workers\Workers;

EventDispatcher::add_event_listener('leantime.core.console.consolekernel.schedule.cron', function ($params) {
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

    $scheduler
        ->call(fn () => app()->make(Services\Queue::class)->processQueue(Workers::DEFAULT))
        ->everyFiveMinutes();
});
