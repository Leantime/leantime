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
        public $sortIndex;

        public $date;
        public string $timelineDate;
        public string $timelineDateToFinish;
        public $dateToFinish;
        public $timeToFinish;
        public $status;
        public $storypoints;
        public $hourRemaining;
        public $planHours;
        public $sprint;
        public $acceptanceCriteria;
        public $tags;
        public $url;
        public $editFrom;
        public $editTo;
        public $dependingTicketId;

        public $projectName;
        public $clientName;
        public $userFirstname;
        public $userLastname;
        public $editorFirstname;
        public $editorLastname;

        public $doneTickets;

        public function __construct($values = false)
        {

            if ($values !== false) {
                $this->id = $values["id"] ?? '';
                $this->headline = $values["headline"] ?? '';
                $this->type = $values["type"] ?? '';
                $this->description = $values["description"] ?? '';
                $this->projectId = $values["projectId"] ?? '';
                $this->editorId = $values["editorId"] ?? '';
                $this->userId = $values["userId"] ?? '';
                $this->priority = $values["priority"] ?? '';

                $this->date = $values["date"] ?? date('Y-m-d  H:i:s');
                $this->dateToFinish = $values["dateToFinish"] ?? '';
                $this->status = $values["status"] ?? '3';
                $this->storypoints = $values["storypoints"] ?? '';
                $this->hourRemaining = $values["hourRemaining"] ?? '';
                $this->planHours = $values["planHours"] ?? '';
                $this->sprint = $values["sprint"] ?? '';
                $this->acceptanceCriteria = $values["acceptanceCriteria"] ?? '';
                $this->tags = $values["tags"] ?? '';
                $this->editFrom = $values["editFrom"] ?? '';
                $this->editTo = $values["editTo"] ?? '';
                $this->dependingTicketId = $values["dependingTicketId"] ?? '';

                $this->projectName = $values["projectName"] ?? '';
                $this->clientName = $values["clientName"] ?? '';
                $this->userFirstname = $values["userFirstname"] ?? '';
                $this->userLastname = $values["userLastname"] ?? '';
                $this->editorFirstname = $values["editorFirstname"] ?? '';
                $this->editorLastname = $values["editorLastname"] ?? '';
            }
        }
    }

}
