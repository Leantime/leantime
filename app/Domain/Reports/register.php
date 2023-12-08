<?php

namespace Leantime\Domain\Reports;

use Leantime\Core\Events;
use Illuminate\Console\Scheduling\Schedule;

Events::add_event_listener('leantime.core.consolekernel.schedule.cron', function ($params) {
    if (get_class($params['schedule']) !== Schedule::class) {
        return;
    }

    $reportService = app()->make(Services\Reports::class);

    $params['schedule']->call(function () use ($reportService) {
        $telemetry = $reportService->sendAnonymousTelemetry();

        Events::add_event_listener('leantime.core.consolekernel.terminate.command', function () use ($telemetry) {
            try {
                $telemetry->wait();
            } catch (\Throwable $e) {
                error_log($e);
            }
        });
    })->daily();

    $params['schedule']->call(fn () => $reportService->dailyIngestion())->daily();
});

