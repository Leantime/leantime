<?php

namespace Leantime\Domain\Queue;

use Leantime\Core\Events;
use Illuminate\Console\Scheduling\Schedule;

Events::add_event_listener('leantime.core.consolekernel.schedule.cron', function ($params) {
    if (get_class($params['schedule']) !== Schedule::class) {
        return;
    }

    $params['schedule']->call(fn () => app()->make(Services\Queue::class)->processQueue())->everyMinute();
});
