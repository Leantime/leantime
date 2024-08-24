<?php

namespace Leantime\Domain\Tickets\Models {

    /**
     *
     */
    class Tickets
    {
        public mixed $id = null;
        public ?string $headline = '';
        public mixed $type = null;
        public ?string $description = '';
        public mixed $projectId = null;
        public mixed $projectDescription = null;
        public mixed $editorId = null;
        public mixed $userId = null;
        public mixed $priority = null;
        public mixed $sortIndex = null;

        public mixed $date = null;
        public mixed $timelineDate = null;
        public mixed $timelineDateToFinish = null;
        public mixed $dateToFinish = null;
        public mixed $timeToFinish = null;
        public mixed $status = 3;
        public mixed $storypoints = null;
        public mixed $hourRemaining = null;
        public mixed $planHours = null;
        public mixed $sprint = null;
        public ?string $acceptanceCriteria = '';
        public mixed $tags = null;
        public mixed $url = null;
        public mixed $editFrom = null;
        public mixed $timeFrom  = null;
        public mixed $editTo  = null;
        public mixed $timeTo = null;
        public mixed $dependingTicketId = null;
        public ?string $parentHeadline = '';
        public mixed $milestoneid = null;

        public ?string $projectName = '';
        public ?string $clientName = '';
        public ?string $userFirstname = '';
        public ?string $userLastname = '';
        public ?string $editorFirstname = '';
        public ?string $editorLastname = '';

        public mixed $doneTickets = null;

        public mixed $allTickets = null;
        public mixed $percentDone = null;
        public mixed $milestoneHeadline = null;
        public mixed $milestoneColor = null;
        public mixed $editorProfileId = null;
        public mixed $bookedHours = null;

        /**
         * @param $values
         */
        /**
         * @param false $values
         */
        public function __construct(array|bool $values = false)
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
