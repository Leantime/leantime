<?php

namespace leantime\domain\models\connector {

    class entity
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
