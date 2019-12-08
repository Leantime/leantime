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
        public $priority;

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

        public $projectName;
        public $clientName;
        public $userFirstname;
        public $userLastname;
        public $editorFirstname;
        public $editorLastname;

        public function __construct()
        {
        }
    }

}
