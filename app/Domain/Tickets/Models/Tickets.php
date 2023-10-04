<?php

namespace Leantime\Domain\Tickets\Models {

    /**
     *
     */
    class Tickets
    {
        public mixed $id = null;
        public mixed $headline = null;
        public mixed $type = null;
        public mixed $description = null;
        public mixed $projectId = null;
        public mixed $editorId = null;
        public mixed $userId = null;
        public mixed $priority = null;
        public $sortIndex = null;

        public mixed $date = null;
        public $timelineDate = null;
        public $timelineDateToFinish = null;
        public mixed $dateToFinish = null;
        public $timeToFinish = null;
        public mixed $status = null;
        public mixed $storypoints = null;
        public mixed $hourRemaining = null;
        public mixed $planHours = null;
        public mixed $sprint = null;
        public mixed $acceptanceCriteria = null;
        public mixed $tags = null;
        public $url = null;
        public mixed $editFrom = null;
        public $timeFrom  = null;
        public mixed $editTo  = null;
        public $timeTo = null;
        public mixed $dependingTicketId = null;
        public $parentHeadline = null;
        public mixed $milestoneid = null;

        public mixed $projectName = null;
        public mixed $clientName = null;
        public mixed $userFirstname = null;
        public mixed $userLastname = null;
        public mixed $editorFirstname = null;
        public mixed $editorLastname = null;

        public $doneTickets = null;

        /**
         * @param $values
         */
        /**
         * @param false $values
         */
        public function __construct(bool $values = false)
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
