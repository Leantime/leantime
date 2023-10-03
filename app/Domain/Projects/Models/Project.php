<?php

namespace Leantime\Domain\Projects\Models {

    /**
     *
     */

    /**
     *
     */
    class Project
    {
        public $id;
        public $name;
        public $clientId;

        public $start;
        public $end;
        public $projectId;
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
