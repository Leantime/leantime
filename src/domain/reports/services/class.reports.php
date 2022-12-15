<?php

namespace leantime\domain\services {

    use GuzzleHttp\Client;
    use GuzzleHttp\Promise\PromiseInterface;
    use leantime\core;
    use leantime\domain\repositories;
    use Ramsey\Uuid\Uuid;

    class reports {

        private $projectRepository;
        private $sprintRepository;
        private $ticketRepository;
        private $reportRepository;

        public function __construct() {

            $this->tpl = new core\template();
            $this->projectRepository = new repositories\projects();
            $this->sprintRepository = new repositories\sprints();
            $this->reportRepository = new repositories\reports();
            $this->settings = new repositories\setting();
            $this->appSettings = new core\appSettings();
            $this->ticketRepository = new repositories\tickets();
        }

        public function dailyIngestion() {

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

        public function getFullReport($projectId) {
            return $this->reportRepository->getFullReport($projectId);
        }

        public function getRealtimeReport($projectId, $sprintId) {
            return $this->reportRepository->runTicketReport($projectId, $sprintId);
        }

        public function getAnonymousTelemetry() {

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
            $this->config = new \leantime\core\environment();
// Canvas: cp, dbm, ea, em, insights, lbm, lean, obm, retros, risks, sb, sm, sq, swot
            $this->cpCanvasRepo = new repositories\cpcanvas();
            $this->dbmCanvasRepo = new repositories\dbmcanvas();
            $this->eaCanvasRepo = new repositories\eacanvas();
            $this->emCanvasRepo = new repositories\emcanvas();
            $this->insightsCanvasRepo = new repositories\insightscanvas();
            $this->lbmCanvasRepo = new repositories\lbmcanvas();
            $this->leanCanvasRepo = new repositories\leancanvas();
            $this->obmCanvasRepo = new repositories\obmcanvas();
            $this->retrosCanvasRepo = new repositories\retroscanvas();
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
                'numIdeaItems' => $this->ideaRepository->getNumberOfIdeas(),
                'numHoursBooked' => $this->timesheetRepo->getHoursBooked(),
                // Canvas: cp, dbm, ea, em, insights, lbm, lean, obm, retros, risks, sb, sm, sq, swot
                'numResearchBoards' => $this->leanCanvasRepo->getNumberOfBoards(),
                'numRetroBoards' => $this->retrosCanvasRepo->getNumberOfBoards(),
                'numCPBoards' => $this->cpCanvasRepo->getNumberOfBoards(),
                'numDBMBoards' => $this->dbmCanvasRepo->getNumberOfBoards(),
                'numEABoards' => $this->eaCanvasRepo->getNumberOfBoards(),
                'numEMBoards' => $this->emCanvasRepo->getNumberOfBoards(),
                'numINSIGHTSBoards' => $this->insightsCanvasRepo->getNumberOfBoards(),
                'numLBMBoards' => $this->lbmCanvasRepo->getNumberOfBoards(),
                'numOBMBoards' => $this->obmCanvasRepo->getNumberOfBoards(),
                'numRISKSBoards' => $this->risksCanvasRepo->getNumberOfBoards(),
                'numSBBoards' => $this->sbCanvasRepo->getNumberOfBoards(),
                'numSMBoards' => $this->smCanvasRepo->getNumberOfBoards(),
                'numSQBoards' => $this->sqCanvasRepo->getNumberOfBoards(),
                'numSWOTBoards' => $this->swotCanvasRepo->getNumberOfBoards(),
                'numResearchItems' => $this->leanCanvasRepo->getNumberOfCanvasItems(),
                'numRetroItems' => $this->retrosCanvasRepo->getNumberOfCanvasItems(),
                'numCPItems' => $this->cpCanvasRepo->getNumberOfCanvasItems(),
                'numDBMItems' => $this->dbmCanvasRepo->getNumberOfCanvasItems(),
                'numEAItems' => $this->eaCanvasRepo->getNumberOfCanvasItems(),
                'numEMItems' => $this->emCanvasRepo->getNumberOfCanvasItems(),
                'numINSIGHTSItems' => $this->insightsCanvasRepo->getNumberOfCanvasItems(),
                'numLBMItems' => $this->lbmCanvasRepo->getNumberOfCanvasItems(),
                'numOBMItems' => $this->obmCanvasRepo->getNumberOfCanvasItems(),
                'numRISKSItems' => $this->risksCanvasRepo->getNumberOfCanvasItems(),
                'numSBItems' => $this->sbCanvasRepo->getNumberOfCanvasItems(),
                'numSMItems' => $this->smCanvasRepo->getNumberOfCanvasItems(),
                'numSQItems' => $this->sqCanvasRepo->getNumberOfCanvasItems(),
                'numSWOTItems' => $this->swotCanvasRepo->getNumberOfCanvasItems()
            );

            return $telemetry;
        }

        public function sendAnonymousTelemetry(): bool|PromiseInterface {

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
