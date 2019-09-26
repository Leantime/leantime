<?php

namespace leantime\domain\models {

    class sprints
    {
        public $id;
        public $name;
        public $startDate;
        public $endDate;
        public $projectId;

        public function __construct()
        {
        }
    }

}