<?php

namespace leantime\domain\models\wiki {

    class article
    {
        public $id;
        public $title;
        public $description;
        public $canvasId;
        public $parent;
        public $tags;
        public $status;
        public $created;
        public $modified;
        public $author;
        public $firstname;
        public $lastname;
        public $profileId;
        public $milestoneId;
        public $milestoneHeadline;
        public $milestoneEditTo;
        public $sortindex;
        public $percentDone;
        public $data;

        public function __construct()
        {
        }
    }

}