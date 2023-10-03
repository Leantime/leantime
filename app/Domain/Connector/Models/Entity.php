<?php

namespace Leantime\Domain\Connector\Models {

    /**
     *
     */

    /**
     *
     */
    class Entity
    {
        public $id;

        public $name;

        public $authData;

        public $notes;

        //Leantime domain object
        public $leantimeEntity;

        //Array of field objects
        public $fieldMappings = [];

        //External domain object
        public $providerEntity;


        public function __construct()
        {
        }
    }

}
