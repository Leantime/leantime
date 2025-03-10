<?php

namespace Leantime\Domain\Reports;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Events\EventDispatcher;

EventDispatcher::addEventListener('leantime.core.console.consolekernel.schedule.cron', function ($params) {

    /** @var Schedule $scheduler */
    if (get_class($scheduler = $params['schedule']) !== Schedule::class) {
        return;
    }

    /** @var Services\Reports $reportService */
    $reportService = app()->make(Services\Reports::class);

    $scheduler->call(function () use ($reportService) {

        $telemetry = $reportService->sendAnonymousTelemetry();

        if ($telemetry === false) {
            return;
        }

        try {

            $response = $telemetry->wait();

        } catch (\Throwable $e) {
            Log::error($e);
        }

    })->name('reports:telemetry')->everyMinute();

    $scheduler->call(function () use ($reportService) {
        $reportService->cronDailyIngestion();
    })->name('reports:dailyIngestion')->everyMinute();
});
