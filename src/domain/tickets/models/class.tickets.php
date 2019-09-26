<?php

namespace leantime\domain\models {

    class tickets
    {
        public $id;
        public $headline;
        public $type;
        public $description;
        public $projectId;
        public $editorId;
        public $userId;

        public $date;
        public $dateToFinish;
        public $status;
        public $storypoints;
        public $hourRemaining;
        public $planHours;
        public $sprint;
        public $acceptanceCriteria;
        public $tags;
        public $editFrom;
        public $editTo;
        public $dependingTicketId;

        public function __construct()
        {
        }
    }

}
