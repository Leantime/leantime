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

            if(!isset($_SESSION["reportCompleted"][$_SESSION['currentProject']]) || $_SESSION["reportCompleted"][$_SESSION['currentProject']] != 1) {

                //Check if the dailyingestion cycle was executed already. There should be one entry for backlog and one entry for current sprint (unless there is no current sprint
                //Get current Sprint Id, if no sprint available, dont run the sprint burndown

                $lastEntries = $this->reportRepository->checkLastReportEntries($_SESSION['currentProject']);

                //If we receive 2 entries we have a report already. If we have one entry then we ran the backlog one and that means there was no current sprint.

                if (count($lastEntries) == 0) {

                    $currentSprint = $this->sprintRepository->getCurrentSprint($_SESSION['currentProject']);

                    if ($currentSprint !== false) {
                        $sprintReport = $this->reportRepository->runTicketReport($_SESSION['currentProject'], $currentSprint->id);
                        if ($sprintReport !== false) {
                            $this->reportRepository->addReport($sprintReport);
                        }
                    }

                    $backlogReport = $this->reportRepository->runTicketReport($_SESSION['currentProject'], "");

                    if ($backlogReport !== false) {
                        $this->reportRepository->addReport($backlogReport);

                        if(!isset($_SESSION["reportCompleted"]) || is_array($_SESSION["reportCompleted"]) === false){
                            $_SESSION["reportCompleted"] = array();
                        }

                        $_SESSION["reportCompleted"][$_SESSION['currentProject']] = 1;
                    }

                }

            }

        }

        public function getFullReport($projectId) {
            return $this->reportRepository->getFullReport($projectId);
        }

        public function getRealtimeReport($projectId, $sprintId) {
            return $this->reportRepository->runTicketReport($projectId, $sprintId);
        }
    }

}
