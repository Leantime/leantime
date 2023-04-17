<?php

use leantime\domain\models\connector\provider;

/**
 * MotivationalQuotes
 *
 * Register Events here
 *
 */

//Create function for the event
class addCSVImportProvider {

    public function handle($payload){

        $provider = new \leantime\plugins\services\csvImport();
        $payload[$provider->id] = $provider;

        return $payload;
    }

}

//Register event listener
\leantime\core\events::add_filter_listener("domain.connector.services.providers.loadProviders.providerList", new addCSVImportProvider());
