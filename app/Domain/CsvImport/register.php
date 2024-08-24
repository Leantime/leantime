<?php

use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\CsvImport\Listeners\AddCSVImportProvider;

//Register event listener
EventDispatcher::add_filter_listener(
    "domain.connector.services.providers.loadProviders.providerList",
    new AddCSVImportProvider()
);
