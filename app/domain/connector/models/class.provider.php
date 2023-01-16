<?php

namespace leantime\domain\models\connector {

    class provider
    {
        public $id;
        public $name;
        public array $availableFields = [];

        public function __construct()
        {
        }
    }

}
