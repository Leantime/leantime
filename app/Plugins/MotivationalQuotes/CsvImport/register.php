<?php

namespace Leantime\Plugins\MotivationalQuotes\CsvImport;

use Leantime\Core\Events;
use Leantime\Plugins\MotivationalQuotes\CsvImport\Services\CsvImport as CsvImportService;

/**
 * MotivationalQuotes
 *
 * Register Events here
 *
 */
//Create function for the event
class AddCSVImportProvider
{
    /**
     * @param $payload
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    /**
     * @param $payload
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handle($payload): mixed
    {

        $provider = app()->make(CsvImportService::class);
        $payload[$provider->id] = $provider;

        return $payload;
    }
}

//Register event listener
Events::add_filter_listener("domain.connector.services.providers.loadProviders.providerList", new addCSVImportProvider());
