<?php

namespace Leantime\Domain\Reports;

use Illuminate\Console\Scheduling\Schedule;
use Leantime\Core\Events\EventDispatcher;

EventDispatcher::add_event_listener('leantime.core.console.consolekernel.schedule.cron', function ($params) {

    /** @var Schedule $scheduler */
    if (get_class($scheduler = $params['schedule']) !== Schedule::class) {
        return;
    }

    /** @var Services\Reports $reportService */
    $reportService = app()->make(Services\Reports::class);

    $scheduler->call(function () use ($reportService) {

        $telemetry = $reportService->sendAnonymousTelemetry();

        if($telemetry === false) return;

        try {
            $response = $telemetry->wait();

        } catch (\Throwable $e) {
            report($e);
        }

    })->everyMinute();

    $scheduler->call(fn () => $reportService->dailyIngestion())->everyMinute();
});
