<?php

use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\CsvImport\Listeners\AddCSVImportProvider;

//Register event listener
EventDispatcher::add_filter_listener(
    'leantime.domain.connector.services.providers.loadProviders.providerList',
    new AddCSVImportProvider
);
