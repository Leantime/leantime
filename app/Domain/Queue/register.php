<?php

namespace Leantime\Domain\Queue;

use Illuminate\Console\Scheduling\Schedule;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Queue\Workers\Workers;

EventDispatcher::addEventListener('leantime.core.console.consolekernel.schedule.cron', function ($params) {

    /** @var Schedule $scheduler */
    if (get_class($scheduler = $params['schedule']) !== Schedule::class) {
        return;
    }

    $scheduler
        ->call(fn () => app()->make(Services\Queue::class)->processQueue(Workers::EMAILS))
        ->name('queue:emails')
        ->everyMinute();

    $scheduler
        ->call(fn () => app()->make(Services\Queue::class)->processQueue(Workers::HTTPREQUESTS))
        ->name('queue:httprequests')
        ->everyFiveMinutes();

    $scheduler
        ->call(fn () => app()->make(Services\Queue::class)->processQueue(Workers::DEFAULT))
        ->name('queue:default')
        ->everyFiveMinutes();
});
