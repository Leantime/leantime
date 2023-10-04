<?php

namespace Leantime\Domain\Reports\Services {

    use DateTime;
    use DateTimeZone;
    use Exception;
    use GuzzleHttp\Client;
    use GuzzleHttp\Promise\PromiseInterface;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Template as TemplateCore;
    use Leantime\Core\AppSettings as AppSettingCore;
    use Leantime\Core\Environment as EnvironmentCore;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Sprints\Repositories\Sprints as SprintRepository;
    use Leantime\Domain\Reports\Repositories\Reports as ReportRepository;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
    use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;
    use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
    use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
    use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
    use Leantime\Domain\Eacanvas\Repositories\Eacanvas as EacanvaRepository;
    use Leantime\Domain\Insightscanvas\Repositories\Insightscanvas as InsightscanvaRepository;
    use Leantime\Domain\Leancanvas\Repositories\Leancanvas as LeancanvaRepository;
    use Leantime\Domain\Obmcanvas\Repositories\Obmcanvas as ObmcanvaRepository;
    use Leantime\Domain\Retroscanvas\Repositories\Retroscanvas as RetroscanvaRepository;
    use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvaRepository;
    use Leantime\Domain\Valuecanvas\Repositories\Valuecanvas as ValuecanvaRepository;
    use Leantime\Domain\Minempathycanvas\Repositories\Minempathycanvas as MinempathycanvaRepository;
    use Leantime\Domain\Riskscanvas\Repositories\Riskscanvas as RiskscanvaRepository;
    use Leantime\Domain\Sbcanvas\Repositories\Sbcanvas as SbcanvaRepository;
    use Leantime\Domain\Swotcanvas\Repositories\Swotcanvas as SwotcanvaRepository;
    use Leantime\Domain\Wiki\Repositories\Wiki as WikiRepository;
    use Ramsey\Uuid\Uuid;
    use Leantime\Core\Eventhelpers;

    /**
     *
     */
    class Reports
    {
        use Eventhelpers;

        private TemplateCore $tpl;
        private AppSettingCore $appSettings;
        private EnvironmentCore $config;
        private ProjectRepository $projectRepository;
        private SprintRepository $sprintRepository;
        private ReportRepository $reportRepository;
        private SettingRepository $settings;
        private TicketRepository $ticketRepository;

        /**
         * @param TemplateCore      $tpl
         * @param AppSettingCore    $appSettings
         * @param EnvironmentCore   $config
         * @param ProjectRepository $projectRepository
         * @param SprintRepository  $sprintRepository
         * @param ReportRepository  $reportRepository
         * @param SettingRepository $settings
         * @param TicketRepository  $ticketRepository
         */
        public function __construct(
            TemplateCore $tpl,
            AppSettingCore $appSettings,
            EnvironmentCore $config,
            ProjectRepository $projectRepository,
            SprintRepository $sprintRepository,
            ReportRepository $reportRepository,
            SettingRepository $settings,
            TicketRepository $ticketRepository
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

        /**
         * @return void
         * @throws BindingResolutionException
         */
        public function dailyIngestion(): void
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
        
        /**
         * @param $projectId
         * @return array|false
         */
        public function getFullReport($projectId): false|array
        {
            return $this->reportRepository->getFullReport($projectId);
        }

        /**
         * @param $projectId
         * @param $sprintId
         * @return array|bool
         * @throws BindingResolutionException
         */
        public function getRealtimeReport($projectId, $sprintId): array|bool
        {
            return $this->reportRepository->runTicketReport($projectId, $sprintId);
        }

        /**
         * @param IdeaRepository            $ideaRepository
         * @param UserRepository            $userRepository
         * @param ClientRepository          $clientRepository
         * @param CommentRepository         $commentsRepository
         * @param TimesheetRepository       $timesheetRepo
         * @param EacanvaRepository         $eaCanvasRepo
         * @param InsightscanvaRepository   $insightsCanvasRepo
         * @param LeancanvaRepository       $leanCanvasRepo
         * @param ObmcanvaRepository        $obmCanvasRepo
         * @param RetroscanvaRepository     $retrosCanvasRepo
         * @param GoalcanvaRepository       $goalCanvasRepo
         * @param ValuecanvaRepository      $valueCanvasRepo
         * @param MinempathycanvaRepository $minEmpathyCanvasRepo
         * @param RiskscanvaRepository      $risksCanvasRepo
         * @param SbcanvaRepository         $sbCanvasRepo
         * @param SwotcanvaRepository       $swotCanvasRepo
         * @param WikiRepository            $wikiRepo
         * @return array
         */
        public function getAnonymousTelemetry(
            IdeaRepository $ideaRepository,
            UserRepository $userRepository,
            ClientRepository $clientRepository,
            CommentRepository $commentsRepository,
            TimesheetRepository $timesheetRepo,
            EacanvaRepository $eaCanvasRepo,
            InsightscanvaRepository $insightsCanvasRepo,
            LeancanvaRepository $leanCanvasRepo,
            ObmcanvaRepository $obmCanvasRepo,
            RetroscanvaRepository $retrosCanvasRepo,
            GoalcanvaRepository $goalCanvasRepo,
            ValuecanvaRepository $valueCanvasRepo,
            MinempathycanvaRepository $minEmpathyCanvasRepo,
            RiskscanvaRepository $risksCanvasRepo,
            SbcanvaRepository $sbCanvasRepo,
            SwotcanvaRepository $swotCanvasRepo,
            WikiRepository $wikiRepo
        ): array {

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
                'numUsers' => $userRepository->getNumberOfUsers(),
                'lastUserLogin' => $userRepository->getLastLogin(),
                'numProjects' => $this->projectRepository->getNumberOfProjects(null, "project"),
                'numStrategies' => $this->projectRepository->getNumberOfProjects(null, "strategy"),
                'numPrograms' => $this->projectRepository->getNumberOfProjects(null, "program"),
                'numClients' => $clientRepository->getNumberOfClients(),
                'numComments' => $commentsRepository->countComments(),
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

            );

            return $telemetry;
        }

        /**
         * @return bool|PromiseInterface
         * @throws BindingResolutionException
         */
        public function sendAnonymousTelemetry(): bool|PromiseInterface
        {

            if (isset($_SESSION['skipTelemetry']) && $_SESSION['skipTelemetry'] === true) {
                return false;
            }

            //Only send once a day
            $allowTelemetry = (bool) $this->settings->getSetting("companysettings.telemetry.active");

            if ($allowTelemetry === true) {
                $date_utc = new DateTime("now", new DateTimeZone("UTC"));
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
                                'telemetry' => $data_string,
                            ],
                            'timeout' => 5,
                        ])->then(function ($response) use ($today) {

                            $this->settings->saveSetting("companysettings.telemetry.lastUpdate", $today);
                            $_SESSION['skipTelemetry'] = true;
                        });

                        return $promise;
                    } catch (Exception $e) {
                        error_log($e);

                        $_SESSION['skipTelemetry'] = true;
                        return false;
                    }
                }
            }

            $_SESSION['skipTelemetry'] = true;
            return false;
        }

        /**
         * @return false|void
         * @throws Exception
         */
        public function optOutTelemetry()
        {
            $date_utc = new DateTime("now", new DateTimeZone("UTC"));
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

                'numINSIGHTSBoards' => 0,
            );

            $telemetry['date'] = $today;

            //Do the curl
            $httpClient = new Client();

            try {
                $data_string = json_encode($telemetry);

                $promise = $httpClient->postAsync("https://telemetry.leantime.io", [
                    'form_params' => [
                        'telemetry' => $data_string,
                    ],
                    'timeout' => 5,
                ])->then(function ($response) use ($today) {

                    $this->settings->saveSetting("companysettings.telemetry.lastUpdate", $today);
                    $_SESSION['skipTelemetry'] = true;
                });
            } catch (Exception $e) {
                error_log($e);

                $_SESSION['skipTelemetry'] = true;
                return false;
            }

            $this->settings->saveSetting("companysettings.telemetry.active", false);

            $_SESSION['skipTelemetry'] = true;

            try {
                $promise->wait();
            } catch (Exception $e) {
                error_log($e);
            }

            return;
        }
    }

}
