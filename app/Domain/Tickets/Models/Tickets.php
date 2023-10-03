<?php

namespace Leantime\Domain\Tickets\Models {

    /**
     *
     */
    class Tickets
    {
        public mixed $id;
        public mixed $headline;
        public mixed $type;
        public mixed $description;
        public mixed $projectId;
        public mixed $editorId;
        public mixed $userId;
        public mixed $priority;
        public $sortIndex;

        public mixed $date;
        public $timelineDate;
        public $timelineDateToFinish;
        public mixed $dateToFinish;
        public $timeToFinish;
        public mixed $status;
        public mixed $storypoints;
        public mixed $hourRemaining;
        public mixed $planHours;
        public mixed $sprint;
        public mixed $acceptanceCriteria;
        public mixed $tags;
        public $url;
        public mixed $editFrom;
        public $timeFrom;
        public mixed $editTo;
        public $timeTo;
        public mixed $dependingTicketId;
        public $parentHeadline;
        public mixed $milestoneid;

        public mixed $projectName;
        public mixed $clientName;
        public mixed $userFirstname;
        public mixed $userLastname;
        public mixed $editorFirstname;
        public mixed $editorLastname;

        public $doneTickets;

        /**
         * @param $values
         */
        /**
         * @param false $values
         */
        public function __construct(false $values = false)
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
                $this->milestoneid = $values["milestoneid"] ?? '';
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
