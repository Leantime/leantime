<?php

namespace leantime\domain\services {

    use GuzzleHttp\Client;
    use GuzzleHttp\Promise\PromiseInterface;
    use leantime\core;
    use leantime\domain\repositories;
    use Ramsey\Uuid\Uuid;

    class reports
    {
        private $projectRepository;
        private $sprintRepository;
        private $ticketRepository;
        private $reportRepository;
        private core\template $tpl;
        private repositories\setting $settings;
        private core\appSettings $appSettings;

        private repositories\ideas $ideaRepository;
        private repositories\users $userRepository;
        private repositories\clients $clientRepository;
        private repositories\comments $commentsRepository;
        private repositories\timesheets $timesheetRepo;
        private \leantime\core\environment $config;

        private repositories\cpcanvas $cpCanvasRepo;
        private repositories\dbmcanvas $dbmCanvasRepo;
        private repositories\eacanvas $eaCanvasRepo;
        private repositories\emcanvas $emCanvasRepo;
        private repositories\insightscanvas $insightsCanvasRepo;
        private repositories\lbmcanvas $lbmCanvasRepo;
        private repositories\leancanvas $leanCanvasRepo;
        private repositories\obmcanvas $obmCanvasRepo;
        private repositories\retroscanvas $retrosCanvasRepo;
        private repositories\riskscanvas $risksCanvasRepo;
        private repositories\sbcanvas $sbCanvasRepo;
        private repositories\smcanvas $smCanvasRepo;
        private repositories\sqcanvas $sqCanvasRepo;
        private repositories\swotcanvas $swotCanvasRepo;
        private repositories\goalcanvas $goalCanvasRepo;
        private repositories\valuecanvas $valueCanvasRepo;
        private repositories\minempathycanvas $minEmpathyCanvasRepo;

        public function __construct()
        {

            $this->tpl = new core\template();
            $this->projectRepository = new repositories\projects();
            $this->sprintRepository = new repositories\sprints();
            $this->reportRepository = new repositories\reports();
            $this->settings = new repositories\setting();
            $this->appSettings = new core\appSettings();
            $this->ticketRepository = new repositories\tickets();
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

        public function getAnonymousTelemetry()
        {

            //Get anonymous company guid
            $companyId = $this->settings->getSetting("companysettings.telemetry.anonymousId");

            if ($companyId === false) {
                $uuid = Uuid::uuid4();
                $companyId = $uuid->toString();
                $this->settings->saveSetting("companysettings.telemetry.anonymousId", $companyId);
            }

            $this->ideaRepository = new repositories\ideas();
            $this->userRepository = new repositories\users();
            $this->clientRepository = new repositories\clients();
            $this->commentsRepository = new repositories\comments();
            $this->timesheetRepo = new repositories\timesheets();

            $this->cpCanvasRepo = new repositories\cpcanvas();
            $this->dbmCanvasRepo = new repositories\dbmcanvas();
            $this->eaCanvasRepo = new repositories\eacanvas();
            $this->emCanvasRepo = new repositories\emcanvas();
            $this->insightsCanvasRepo = new repositories\insightscanvas();
            $this->lbmCanvasRepo = new repositories\lbmcanvas();
            $this->leanCanvasRepo = new repositories\leancanvas();
            $this->obmCanvasRepo = new repositories\obmcanvas();
            $this->retrosCanvasRepo = new repositories\retroscanvas();
            $this->goalCanvasRepo = new repositories\goalcanvas();
            $this->valueCanvasRepo = new repositories\valuecanvas();
            $this->minEmpathyCanvasRepo = new repositories\minempathycanvas();

            $this->risksCanvasRepo = new repositories\riskscanvas();
            $this->sbCanvasRepo = new repositories\sbcanvas();
            $this->smCanvasRepo = new repositories\smcanvas();
            $this->sqCanvasRepo = new repositories\sqcanvas();
            $this->swotCanvasRepo = new repositories\swotcanvas();

            $companyLang = $this->settings->getSetting("companysettings.language");
            if ($companyLang != "" && $companyLang !== false) {
                $currentLanguage = $companyLang;
            } else {
                $currentLanguage = $this->config->language;
            }

            $telemetry = array(
                'date' => '',
                'companyId' => $companyId,
                'version' => $this->appSettings->appVersion,
                'language' => $currentLanguage,
                'numUsers' => $this->userRepository->getNumberOfUsers(),
                'lastUserLogin' => $this->userRepository->getLastLogin(),
                'numProjects' => $this->projectRepository->getNumberOfProjects(),
                'numClients' => $this->clientRepository->getNumberOfClients(),
                'numComments' => $this->commentsRepository->countComments(),
                'numMilestones' => $this->ticketRepository->getNumberOfMilestones(),
                'numTickets' => $this->ticketRepository->getNumberOfAllTickets(),

                'numBoards' => $this->ideaRepository->getNumberOfBoards(),

                'numIdeaItems' => $this->ideaRepository->getNumberOfIdeas(),
                'numHoursBooked' => $this->timesheetRepo->getHoursBooked(),

                'numResearchBoards' => $this->leanCanvasRepo->getNumberOfBoards(),
                'numResearchItems' => $this->leanCanvasRepo->getNumberOfCanvasItems(),

                'numRetroBoards' => $this->retrosCanvasRepo->getNumberOfBoards(),
                'numRetroItems' => $this->retrosCanvasRepo->getNumberOfCanvasItems(),

                'numGoalBoards' => $this->goalCanvasRepo->getNumberOfBoards(),
                'numGoalItems' => $this->goalCanvasRepo->getNumberOfCanvasItems(),

                'numValueCanvasBoards' => $this->valueCanvasRepo->getNumberOfBoards(),
                'numValueCanvasItems' => $this->valueCanvasRepo->getNumberOfCanvasItems(),

                'numMinEmpathyBoards' => $this->minEmpathyCanvasRepo->getNumberOfBoards(),
                'numMinEmpathyItems' => $this->minEmpathyCanvasRepo->getNumberOfCanvasItems(),

                'numOBMBoards' => $this->obmCanvasRepo->getNumberOfBoards(),
                'numOBMItems' => $this->obmCanvasRepo->getNumberOfCanvasItems(),

                'numSWOTBoards' => $this->swotCanvasRepo->getNumberOfBoards(),
                'numSWOTItems' => $this->swotCanvasRepo->getNumberOfCanvasItems(),

                'numSBBoards' => $this->sbCanvasRepo->getNumberOfBoards(),
                'numSBItems' => $this->sbCanvasRepo->getNumberOfCanvasItems(),

                'numRISKSBoards' => $this->risksCanvasRepo->getNumberOfBoards(),
                'numRISKSItems' => $this->risksCanvasRepo->getNumberOfCanvasItems(),

                'numEABoards' => $this->eaCanvasRepo->getNumberOfBoards(),
                'numEAItems' => $this->eaCanvasRepo->getNumberOfCanvasItems(),

                'numINSIGHTSBoards' => $this->insightsCanvasRepo->getNumberOfBoards(),
                'numINSIGHTSItems' => $this->insightsCanvasRepo->getNumberOfCanvasItems(),


                /*

                'numCPBoards' => $this->cpCanvasRepo->getNumberOfBoards(),
                'numCPItems' => $this->cpCanvasRepo->getNumberOfCanvasItems(),

                'numDBMBoards' => $this->dbmCanvasRepo->getNumberOfBoards(),
                'numDBMItems' => $this->dbmCanvasRepo->getNumberOfCanvasItems(),

                'numEMBoards' => $this->emCanvasRepo->getNumberOfBoards(),
                'numEMItems' => $this->emCanvasRepo->getNumberOfCanvasItems(),

                'numLBMBoards' => $this->lbmCanvasRepo->getNumberOfBoards(),
                'numLBMItems' => $this->lbmCanvasRepo->getNumberOfCanvasItems(),

                'numSMBoards' => $this->smCanvasRepo->getNumberOfBoards(),
                'numSMItems' => $this->smCanvasRepo->getNumberOfCanvasItems(),

                'numSQBoards' => $this->sqCanvasRepo->getNumberOfBoards(),
                'numSQItems' => $this->sqCanvasRepo->getNumberOfCanvasItems(),


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

            if ($allowTelemetry === true) {
                $date_utc = new \DateTime("now", new \DateTimeZone("UTC"));
                $today = $date_utc->format("Y-m-d");
                $lastUpdate = $this->settings->getSetting("companysettings.telemetry.lastUpdate");

                if ($lastUpdate != $today) {
                    $telemetry = $this->getAnonymousTelemetry();
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
    }

}
