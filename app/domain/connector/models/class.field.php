<?php

namespace leantime\domain\models\connector {

    class field
    {
        public int $id;
        public int $entityConnectionId;
        public string $leantimeFields;
        public string $providerEntity;
        public $typeConnector;


        public function __construct()
        {
        }
    }

}
