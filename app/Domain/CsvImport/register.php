<?php

use Illuminate\Console\Scheduling\Schedule;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\CsvImport\Services\ImportFiles;

// Register event listener
EventDispatcher::add_filter_listener(
    'leantime.domain.connector.services.providers.loadProviders.providerList',
    function (mixed $payload) {

        $provider = app()->make(\Leantime\Domain\CsvImport\Services\CsvImport::class);
        $payload[$provider->id] = $provider;

        return $payload;
    }
);

// Uploaded import files and reviewed rows are temporary: remove the ones older than a day.
EventDispatcher::add_event_listener('leantime.core.console.consolekernel.schedule.cron', function ($params) {

    if (get_class($scheduler = $params['schedule']) !== Schedule::class) {
        return;
    }

    $scheduler
        ->call(fn () => app()->make(ImportFiles::class)->deleteExpired())
        ->daily()
        ->name('csvImport:cleanup');
});
