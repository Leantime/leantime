<?php

use Leantime\Domain\Connector\Models\Provider;
use Leantime\Plugins\CsvImport\Services\CsvImport as CsvImportService;

/**
 * MotivationalQuotes
 *
 * Register Events here
 *
 */

//Create function for the event
class AddCSVImportProvider
{
    public function handle($payload)
    {

        $provider = app()->make(CsvImportService::class);
        $payload[$provider->id] = $provider;

        return $payload;
    }
}

//Register event listener
\Leantime\Core\Events::add_filter_listener("domain.connector.services.providers.loadProviders.providerList", new addCSVImportProvider());
