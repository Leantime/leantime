<?php

use Leantime\Core\Events;
use Leantime\Domain\CsvImport\Listeners\AddCSVImportProvider;

//Register event listener
Events::add_filter_listener(
    "domain.connector.services.providers.loadProviders.providerList",
    new AddCSVImportProvider()
);
