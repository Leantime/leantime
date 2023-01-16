<?php

namespace leantime\domain\models\connector {

    class entity
    {
        public $id;
        public $name;
        public $authData;
        public $notes;

        public $leantimeEntity;
        //Array of field objects
        public $fieldMappings = [];
        public $providerEntity;


        public function __construct()
        {
        }
    }

}
