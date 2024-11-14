<?php

namespace Leantime\Domain\Connector\Models {

    class Field
    {
        public int $id;

        public int $entityConnectionId;

        public string $leantimeFields;

        public string $providerEntity;

        public string $typeConnector;

        public function __construct() {}
    }

}
