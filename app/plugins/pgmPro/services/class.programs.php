<?php

namespace leantime\plugins\services\pgmPro {

    use leantime\core;
    use leantime\domain\repositories;
    use DatePeriod;
    use DateTime;
    use DateInterval;
    use leantime\domain\services\comments;
    use leantime\domain\services\projects;
    use leantime\domain\services\reports;
    use leantime\domain\services\tickets;

    class programs
    {
        private repositories\projects $projectRepository;
        private \leantime\plugins\repositories\pgmPro\programs $programRepository;

        public function __construct()
        {

            $this->tpl = new core\template();
            $this->projectRepository = new repositories\projects();
            $this->projectService = new projects();
            $this->ticketService = new tickets();
            $this->programRepository = new  \leantime\plugins\repositories\pgmPro\programs();
            $this->language = core\language::getInstance();
            $this->commentService = new comments();
            $this->reportService = new reports();
        }

        /**
         * getStatusLabels - Gets all status labels for the current set project
         *
         * @access public
         * @return array
         */
        public function getStatusLabels($projectId = null): array
        {

            return $this->programRepository->getStateLabels($projectId);
        }

        public function getAllProgramProjects(int $programId): array|false {

            $projectResults = array();
            $i = 0;

            $clientId = "";
            $currentClientName = "";

            $allprojects = $this->programRepository->getAllProgramProjects($programId);
            $clients = array();

            if (is_array($allprojects)) {
                foreach ($allprojects as $project) {
                    if (!array_key_exists($project->clientId, $clients)) {
                        $clients[$project->clientId] = $project->clientName;
                    }

                    if ($clientId == "" || $project->clientId == $clientId) {

                        $projectResults[$i] = $project;
                        $projectResults[$i]->progress = $this->projectService->getProjectProgress($project->id);
                        $projectResults[$i]->milestones = $this->ticketService->getAllMilestones($project->id);
                        $projectComment = $this->commentService->getComments("project", $project->id);

                        if (is_array($projectComment) && count($projectComment) > 0) {
                            $projectResults[$i]->lastUpdate = $projectComment[0];
                        } else {
                            $projectResults[$i]->lastUpdate = false;
                        }

                        $fullReport = $this->reportService->getRealtimeReport($project->id, "");

                        $projectResults[$i]->report = $fullReport;

                        $i++;
                    }
                }
            }

            return $projectResults;
        }


        public function getUserPrograms(int $userId): array|false {
            return $this->programRepository->getUserPrograms($userId);
        }

    }

}
