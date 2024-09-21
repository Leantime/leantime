<?php

namespace Leantime\Domain\Projects\Models {

    /**
     *
     */
    class Project
    {
        public int|string $id;
        public $name;
        public null|int|string $clientId;

        public $start;
        public $end;
        public int|string $projectId;
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

        public $clientName;

        public $isFavorite;



        public function __construct()
        {
        }
    }

}
