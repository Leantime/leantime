<?php

namespace leantime\domain\services {

    use leantime\core;
    use leantime\domain\repositories;

    class reports
    {

        private $projectRepository;
        private $sprintRepository;
        private $ticketRepository;
        private $reportRepository;

        public function __construct()
        {

            $this->tpl = new core\template();
            $this->projectRepository = new repositories\projects();
            $this->sprintRepository = new repositories\sprints();
            $this->reportRepository = new repositories\reports();
        }

        public function dailyIngestion()
        {


            //Check if the dailyingestion cycle was executed already. There should be one entry for backlog and one entry for current sprint (unless there is no current sprint
            //Get current Sprint Id, if no sprint available, dont run the sprint burndown

            $lastEntries = $this->reportRepository->checkLastReportEntries($_SESSION['currentProject']);

            //If we receive 2 entries we have a report already. If we have one entry then we ran the backlog one and that means there was no current sprint.

            if(count($lastEntries) == 0) {

                $currentSprint = $this->sprintRepository->getCurrentSprint($_SESSION['currentProject']);

                if ($currentSprint !== false) {
                    $sprintReport = $this->reportRepository->runTicketReport($_SESSION['currentProject'], $currentSprint->id);
                    if($sprintReport !== false) {
                        $this->reportRepository->addReport($sprintReport);
                    }
                }

                $backlogReport = $this->reportRepository->runTicketReport($_SESSION['currentProject'], "");

                if($backlogReport !== false) {
                    $this->reportRepository->addReport($backlogReport);
                }

            }

        }

    }

}
