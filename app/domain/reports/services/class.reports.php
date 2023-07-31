<?php

namespace leantime\domain\services {

    use GuzzleHttp\Client;
    use GuzzleHttp\Promise\PromiseInterface;
    use leantime\core;
    use leantime\domain\repositories;
    use Ramsey\Uuid\Uuid;
    use leantime\core\eventhelpers;

    class reports
    {
        use eventhelpers;

        private core\template $tpl;
        private core\appSettings $appSettings;
        private core\environment $config;
        private repositories\projects $projectRepository;
        private repositories\sprints $sprintRepository;
        private repositories\reports $reportRepository;
        private repositories\setting $settings;
        private repositories\tickets $ticketRepository;

        public function __construct(
            core\template $tpl,
            core\appSettings $appSettings,
            core\environment $config,
            repositories\projects $projectRepository,
            repositories\sprints $sprintRepository,
            repositories\reports $reportRepository,
            repositories\setting $settings,
            repositories\tickets $ticketRepository
        ) {
            $this->tpl = $tpl;
            $this->appSettings = $appSettings;
            $this->config = $config;
            $this->projectRepository = $projectRepository;
            $this->sprintRepository = $sprintRepository;
            $this->reportRepository = $reportRepository;
            $this->settings = $settings;
            $this->ticketRepository = $ticketRepository;
        }

        public function dailyIngestion()
        {

            if (!isset($_SESSION["reportCompleted"][$_SESSION['currentProject']]) || $_SESSION["reportCompleted"][$_SESSION['currentProject']] != 1) {
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

                        if (!isset($_SESSION["reportCompleted"]) || is_array($_SESSION["reportCompleted"]) === false) {
                            $_SESSION["reportCompleted"] = array();
                        }

                        $_SESSION["reportCompleted"][$_SESSION['currentProject']] = 1;
                    }
                }
            }
        }

        public function getFullReport($projectId)
        {
            return $this->reportRepository->getFullReport($projectId);
        }

        public function getRealtimeReport($projectId, $sprintId)
        {
            return $this->reportRepository->runTicketReport($projectId, $sprintId);
        }

        public function getAnonymousTelemetry(
            repositories\ideas $ideaRepository,
            repositories\users $userRepository,
            repositories\clients $clientRepository,
            repositories\comments $commentsRepository,
            repositories\timesheets $timesheetRepo,
            repositories\cpcanvas $cpCanvasRepo,
            repositories\dbmcanvas $dbmCanvasRepo,
            repositories\eacanvas $eaCanvasRepo,
            repositories\emcanvas $emCanvasRepo,
            repositories\insightscanvas $insightsCanvasRepo,
            repositories\lbmcanvas $lbmCanvasRepo,
            repositories\leancanvas $leanCanvasRepo,
            repositories\obmcanvas $obmCanvasRepo,
            repositories\retroscanvas $retrosCanvasRepo,
            repositories\goalcanvas $goalCanvasRepo,
            repositories\valuecanvas $valueCanvasRepo,
            repositories\minempathycanvas $minEmpathyCanvasRepo,
            repositories\riskscanvas $risksCanvasRepo,
            repositories\sbcanvas $sbCanvasRepo,
            repositories\smcanvas $smCanvasRepo,
            repositories\sqcanvas $sqCanvasRepo,
            repositories\swotcanvas $swotCanvasRepo,
            repositories\wiki $wikiRepo
        ) {

            //Get anonymous company guid
            $companyId = $this->settings->getSetting("companysettings.telemetry.anonymousId");

            if ($companyId === false) {
                $uuid = Uuid::uuid4();
                $companyId = $uuid->toString();
                $this->settings->saveSetting("companysettings.telemetry.anonymousId", $companyId);
            }

            self::dispatch_event("beforeTelemetrySend", $companyId);

            $companyLang = $this->settings->getSetting("companysettings.language");
            if ($companyLang != "" && $companyLang !== false) {
                $currentLanguage = $companyLang;
            } else {
                $currentLanguage = $this->config->language;
            }

            $telemetry = array(
                'date' => '',
                'companyId' => $companyId,
                'env' => 'prod',
                'version' => $this->appSettings->appVersion,
                'language' => $currentLanguage,
                'numUsers' => $this->userRepository->getNumberOfUsers(),
                'lastUserLogin' => $this->userRepository->getLastLogin(),
                'numProjects' => $this->projectRepository->getNumberOfProjects(null, "project"),
                'numStrategies' => $this->projectRepository->getNumberOfProjects(null, "strategy"),
                'numPrograms' => $this->projectRepository->getNumberOfProjects(null, "program"),
                'numClients' => $this->clientRepository->getNumberOfClients(),
                'numComments' => $this->commentsRepository->countComments(),
                'numMilestones' => $this->ticketRepository->getNumberOfMilestones(),
                'numTickets' => $this->ticketRepository->getNumberOfAllTickets(),

                'numBoards' => $ideaRepository->getNumberOfBoards(),

                'numIdeaItems' => $ideaRepository->getNumberOfIdeas(),
                'numHoursBooked' => $timesheetRepo->getHoursBooked(),

                'numResearchBoards' => $leanCanvasRepo->getNumberOfBoards(),
                'numResearchItems' => $leanCanvasRepo->getNumberOfCanvasItems(),

                'numRetroBoards' => $retrosCanvasRepo->getNumberOfBoards(),
                'numRetroItems' => $retrosCanvasRepo->getNumberOfCanvasItems(),

                'numGoalBoards' => $goalCanvasRepo->getNumberOfBoards(),
                'numGoalItems' => $goalCanvasRepo->getNumberOfCanvasItems(),

                'numValueCanvasBoards' => $valueCanvasRepo->getNumberOfBoards(),
                'numValueCanvasItems' => $valueCanvasRepo->getNumberOfCanvasItems(),

                'numMinEmpathyBoards' => $minEmpathyCanvasRepo->getNumberOfBoards(),
                'numMinEmpathyItems' => $minEmpathyCanvasRepo->getNumberOfCanvasItems(),

                'numOBMBoards' => $obmCanvasRepo->getNumberOfBoards(),
                'numOBMItems' => $obmCanvasRepo->getNumberOfCanvasItems(),

                'numSWOTBoards' => $swotCanvasRepo->getNumberOfBoards(),
                'numSWOTItems' => $swotCanvasRepo->getNumberOfCanvasItems(),

                'numSBBoards' => $sbCanvasRepo->getNumberOfBoards(),
                'numSBItems' => $sbCanvasRepo->getNumberOfCanvasItems(),

                'numRISKSBoards' => $risksCanvasRepo->getNumberOfBoards(),
                'numRISKSItems' => $risksCanvasRepo->getNumberOfCanvasItems(),

                'numEABoards' => $eaCanvasRepo->getNumberOfBoards(),
                'numEAItems' => $eaCanvasRepo->getNumberOfCanvasItems(),

                'numINSIGHTSBoards' => $insightsCanvasRepo->getNumberOfBoards(),
                'numINSIGHTSItems' => $insightsCanvasRepo->getNumberOfCanvasItems(),

                'numWikiBoards' => $wikiRepo->getNumberOfBoards(),
                'numWikiItems' => $wikiRepo->getNumberOfCanvasItems(),


                /*

                'numCPBoards' => $cpCanvasRepo->getNumberOfBoards(),
                'numCPItems' => $cpCanvasRepo->getNumberOfCanvasItems(),

                'numDBMBoards' => $dbmCanvasRepo->getNumberOfBoards(),
                'numDBMItems' => $dbmCanvasRepo->getNumberOfCanvasItems(),

                'numEMBoards' => $emCanvasRepo->getNumberOfBoards(),
                'numEMItems' => $emCanvasRepo->getNumberOfCanvasItems(),

                'numLBMBoards' => $lbmCanvasRepo->getNumberOfBoards(),
                'numLBMItems' => $lbmCanvasRepo->getNumberOfCanvasItems(),

                'numSMBoards' => $smCanvasRepo->getNumberOfBoards(),
                'numSMItems' => $smCanvasRepo->getNumberOfCanvasItems(),

                'numSQBoards' => $sqCanvasRepo->getNumberOfBoards(),
                'numSQItems' => $sqCanvasRepo->getNumberOfCanvasItems(),


                */



            );

            return $telemetry;
        }

        public function sendAnonymousTelemetry(): bool|PromiseInterface
        {

            if (isset($_SESSION['skipTelemetry']) && $_SESSION['skipTelemetry'] === true) {
                return false;
            }

            //Only send once a day
            $allowTelemetry = (bool) $this->settings->getSetting("companysettings.telemetry.active");
            $allowTelemetry = true;

            if ($allowTelemetry === true) {
                $date_utc = new \DateTime("now", new \DateTimeZone("UTC"));
                $today = $date_utc->format("Y-m-d");
                $lastUpdate = $this->settings->getSetting("companysettings.telemetry.lastUpdate");

                if ($lastUpdate != $today) {
                    $telemetry = app()->call([$this, 'getAnonymousTelemetry']);
                    $telemetry['date'] = $today;

                    //Do the curl
                    $httpClient = new Client();

                    try {
                        $data_string = json_encode($telemetry);

                        $promise = $httpClient->postAsync("https://telemetry.leantime.io", [
                                    'form_params' => [
                                        'telemetry' => $data_string
                                    ],
                                    'timeout' => 5
                                ])->then(function ($response) use ($today) {

                                    $this->settings->saveSetting("companysettings.telemetry.lastUpdate", $today);
                                    $_SESSION['skipTelemetry'] = true;
                                });

                        return $promise;
                    } catch (\Exception $e) {
                        error_log($e);

                        $_SESSION['skipTelemetry'] = true;
                        return false;
                    }
                }
            }

            $_SESSION['skipTelemetry'] = true;
            return false;
        }

        public function optOutTelemetry()
        {
            $date_utc = new \DateTime("now", new \DateTimeZone("UTC"));
            $today = $date_utc->format("Y-m-d");

            $companyId = $this->settings->getSetting("companysettings.telemetry.anonymousId");
            if ($companyId === false) {
                $uuid = Uuid::uuid4();
                $companyId = $uuid->toString();
                $this->settings->saveSetting("companysettings.telemetry.anonymousId", $companyId);
            }

            $telemetry = array(
                'date' => '',
                'companyId' => $companyId,
                'version' => $this->appSettings->appVersion,
                'language' => '',
                'numUsers' => 0,
                'lastUserLogin' => 0,
                'numProjects' => 0,
                'numClients' => 0,
                'numComments' => 0,
                'numMilestones' => 0,
                'numTickets' => 0,

                'numBoards' => 0,

                'numIdeaItems' => 0,
                'numHoursBooked' => 0,

                'numResearchBoards' => 0,
                'numResearchItems' => 0,

                'numRetroBoards' => 0,
                'numRetroItems' => 0,

                'numGoalBoards' => 0,
                'numGoalItems' => 0,

                'numValueCanvasBoards' => 0,
                'numValueCanvasItems' => 0,

                'numMinEmpathyBoards' => 0,
                'numMinEmpathyItems' => 0,

                'numOBMBoards' => 0,
                'numOBMItems' => 0,

                'numSWOTBoards' => 0,
                'numSWOTItems' => 0,

                'numSBBoards' => 0,
                'numSBItems' => 0,

                'numRISKSBoards' => 0,
                'numRISKSItems' => 0,

                'numEABoards' => 0,
                'numEAItems' => 0,

                'numINSIGHTSBoards' => 0
            );

            $telemetry['date'] = $today;

            //Do the curl
            $httpClient = new Client();

            try {
                $data_string = json_encode($telemetry);

                $promise = $httpClient->postAsync("https://telemetry.leantime.io", [
                    'form_params' => [
                        'telemetry' => $data_string
                    ],
                    'timeout' => 5
                ])->then(function ($response) use ($today) {

                    $this->settings->saveSetting("companysettings.telemetry.lastUpdate", $today);
                    $_SESSION['skipTelemetry'] = true;
                });

            } catch (\Exception $e) {
                error_log($e);

                $_SESSION['skipTelemetry'] = true;
                return false;
            }

            $this->settings->saveSetting("companysettings.telemetry.active", false);

            $_SESSION['skipTelemetry'] = true;

            try {
                $promise->wait();
            } catch (\Exception $e) {
                error_log($e);
            }

            return;

        }
    }

}
