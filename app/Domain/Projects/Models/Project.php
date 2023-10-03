<?php

namespace Leantime\Domain\Projects\Models {

    /**
     *
     */
    class Project
    {
        public int $id;
        public $name;
        public int $clientId;

        public $start;
        public $end;
        public int $projectId;
        public $type;
        public $state;
        public $menuType;
        public $numberOfTickets;

        public $sortIndex;

        public $progress;

        public $milestones;

        public $lastUpdate;

        public $report;

        public $status;



        public function __construct()
        {
        }
    }

}
