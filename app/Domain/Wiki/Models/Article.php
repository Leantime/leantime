<?php

namespace Leantime\Domain\Wiki\Models {

    class Article
    {
        public $id;

        public $title;

        public $description;

        public $canvasId;

        public $parent;

        public $tags;

        public $data;

        public $status;

        public $created;

        public $modified;

        public $author;

        public $milestoneId;

        public $firstname;

        public $lastname;

        public $profileId;

        public $sortindex;

        public $projectId;

        public $milestoneHeadline;

        public $milestoneEditTo;

        public $doneTickets;

        public $openTicketsEffort;

        public $doneTicketsEffort;

        public $allTicketsEffort;

        public $allTickets;

        public $percentDone;

        public function __construct() {}
    }

}
