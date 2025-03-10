<?php

use Leantime\Core\Events\EventDispatcher;

//Register event listener
EventDispatcher::addFilterListener(
    'leantime.domain.connector.services.providers.loadProviders.providerList',
    function (mixed $payload) {

        $provider = app()->make(\Leantime\Domain\CsvImport\Services\CsvImport::class);
        $payload[$provider->id] = $provider;

        return $payload;
    }
);
