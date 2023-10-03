<?php

namespace Leantime\Domain\Connector\Models {

    /**
     *
     */
    class Provider
    {
        //Unique identifier of provider
        public $id;

        //Friendly name
        public string $name;

        //Image to show in UI
        public string $image;

        //Entities available to sync/import as part of this provider
        //This should be a list of strings with the exact entity name as they appear in the provider api
        //project, issue, epic, ticket or similar
        public array $availableEntities = [];

        public array $availableMethods = []; //import and/or sync

        public function __construct()
        {
        }
    }

}
