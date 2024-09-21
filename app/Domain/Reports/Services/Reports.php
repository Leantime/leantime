<?php

namespace Leantime\Domain\Reports\Services {

    use DateTime;
    use DateTimeZone;
    use Exception;
    use GuzzleHttp\Client;
    use GuzzleHttp\Promise\PromiseInterface;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Configuration\AppSettings as AppSettingCore;
    use Leantime\Core\Configuration\Environment as EnvironmentCore;
    use Leantime\Core\Events\DispatchesEvents;
    use Leantime\Core\Template as TemplateCore;
    use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
    use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
    use Leantime\Domain\Eacanvas\Repositories\Eacanvas as EacanvaRepository;
    use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvaRepository;
    use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
    use Leantime\Domain\Insightscanvas\Repositories\Insightscanvas as InsightscanvaRepository;
    use Leantime\Domain\Leancanvas\Repositories\Leancanvas as LeancanvaRepository;
    use Leantime\Domain\Minempathycanvas\Repositories\Minempathycanvas as MinempathycanvaRepository;
    use Leantime\Domain\Obmcanvas\Repositories\Obmcanvas as ObmcanvaRepository;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Reactions\Repositories\Reactions;
    use Leantime\Domain\Reports\Repositories\Reports as ReportRepository;
    use Leantime\Domain\Retroscanvas\Repositories\Retroscanvas as RetroscanvaRepository;
    use Leantime\Domain\Riskscanvas\Repositories\Riskscanvas as RiskscanvaRepository;
    use Leantime\Domain\Sbcanvas\Repositories\Sbcanvas as SbcanvaRepository;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use Leantime\Domain\Setting\Services\Setting as SettingsService;
    use Leantime\Domain\Sprints\Repositories\Sprints as SprintRepository;
    use Leantime\Domain\Swotcanvas\Repositories\Swotcanvas as SwotcanvaRepository;
    use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
    use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;
    use Leantime\Domain\Valuecanvas\Repositories\Valuecanvas as ValuecanvaRepository;
    use Leantime\Domain\Wiki\Repositories\Wiki as WikiRepository;

    /**
     *
     *
     * @api
     */
    class Reports
    {
        use DispatchesEvents;

        private TemplateCore $tpl;
        private AppSettingCore $appSettings;
        private EnvironmentCore $config;
        private ProjectRepository $projectRepository;
        private SprintRepository $sprintRepository;
        private ReportRepository $reportRepository;
        private SettingsService $settings;
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
         *
     */
        public function __construct(
            TemplateCore $tpl,
            AppSettingCore $appSettings,
            EnvironmentCore $config,
            ProjectRepository $projectRepository,
            SprintRepository $sprintRepository,
            ReportRepository $reportRepository,
            SettingsService $settings,
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
         *
     * @api
     */
        public function dailyIngestion(): void
        {

            if (
                    session()->exists("currentProject")
                    &&
                    (
                        !session()->exists("reportCompleted.".session("currentProject"))
                        || session("reportCompleted.".session("currentProject")) != 1
                    )
                )
                {
                //Check if the dailyingestion cycle was executed already. There should be one entry for backlog and one entry for current sprint (unless there is no current sprint
                //Get current Sprint Id, if no sprint available, dont run the sprint burndown

                $lastEntries = $this->reportRepository->checkLastReportEntries(session("currentProject"));

                //If we receive 2 entries we have a report already. If we have one entry then we ran the backlog one and that means there was no current sprint.

                if (count($lastEntries) == 0) {
                    $currentSprint = $this->sprintRepository->getCurrentSprint(session("currentProject"));

                    if ($currentSprint !== false) {
                        $sprintReport = $this->reportRepository->runTicketReport(session("currentProject"), $currentSprint->id);
                        if ($sprintReport !== false) {
                            $this->reportRepository->addReport($sprintReport);
                        }
                    }

                    $backlogReport = $this->reportRepository->runTicketReport(session("currentProject"), "");

                    if ($backlogReport !== false) {
                        $this->reportRepository->addReport($backlogReport);

                        if (!session()->exists("reportCompleted") || is_array(session("reportCompleted")) === false) {
                            session(["reportCompleted" => array()]);
                        }

                        session(["reportCompleted.".session("currentProject") => 1]);
                    }
                }
            }
        }

        /**
         * @param $projectId
         * @return array|false
         *
     * @api
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
         *
     * @api
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
         *
     * @api
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
            $companyId = $this->settings->getCompanyId();

            self::dispatch_event("beforeTelemetrySend", array("companyId" => $companyId));

            $companyLang = $this->settings->getSetting("companysettings.language");
            if ($companyLang != "" && $companyLang !== false) {
                $currentLanguage = $companyLang;
            } else {
                $currentLanguage = $this->config->language;
            }

            $projectStatusCount = $this->getProjectStatusReport();

            $taskSentiment = $this->generateTicketReactionsReport();

            $telemetry = array(
                'date' => '',
                'companyId' => $companyId,
                'env' => 'oss',
                'version' => $this->appSettings->appVersion,
                'language' => $currentLanguage,
                'numUsers' => $userRepository->getNumberOfUsers(),
                'lastUserLogin' => $userRepository->getLastLogin(),

                'numProjects' => $this->projectRepository->getNumberOfProjects(null, "project"),
                'numProjectsGreen' => $projectStatusCount["green"] ?? 0,
                'numProjectsYellow' => $projectStatusCount["yellow"] ?? 0,
                'numProjectsRed' => $projectStatusCount["red"] ?? 0,
                'numProjectsNone' => $projectStatusCount["none"] ?? 0,

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

                "numTaskSentimentAngry" => $taskSentiment["🤬"] ?? 0,
                "numTaskSentimentDisgust" => $taskSentiment["🤢"] ?? 0,
                "numTaskSentimentUnhappy" => $taskSentiment["🙁"] ?? 0,
                "numTaskSentimentNeutral" => $taskSentiment["😐"] ?? 0,
                "numTaskSentimentHappy" => $taskSentiment["🙂"] ?? 0,
                "numTaskSentimentLove" => $taskSentiment["😍"] ?? 0,
                "numTaskSentimenUnicorn" => $taskSentiment["🦄"] ?? 0,

                "serverSoftware"    => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
                "phpUname"          => php_uname(),
                "isDocker"          => is_file("/.dockerenv"),
                "phpSapiName"       => php_sapi_name(),
                "phpOs"             => PHP_OS ?? "unknown"

            );

            $telemetry = self::dispatch_filter("beforeReturnTelemetry", $telemetry);

            return $telemetry;
        }

        /**
         * @return bool|PromiseInterface
         * @throws BindingResolutionException
         *
     * @api
     */
        public function sendAnonymousTelemetry(): bool|PromiseInterface
        {

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
                            'timeout' => 480,
                        ])->then(function ($response) use ($today) {
                            $this->settings->saveSetting("companysettings.telemetry.lastUpdate", $today);
                        });

                        return $promise;
                    } catch (\Exception $e) {
                        report($e);
                        return false;
                    }
                }
            }

            return false;
        }

        /**
         * @return false|void
         * @throws Exception
         *
     * @api
     */
        public function optOutTelemetry()
        {
            $date_utc = new DateTime("now", new DateTimeZone("UTC"));
            $today = $date_utc->format("Y-m-d");

            $companyId = $this->settings->getCompanyId();

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
                    session(["skipTelemetry" => true]);
                });
            } catch (\Exception $e) {
                report($e);

                session(["skipTelemetry" => true]);
                return false;
            }

            $this->settings->saveSetting("companysettings.telemetry.active", false);

            session(["skipTelemetry" => true]);

            try {
                $promise->wait();
            } catch (\Exception $e) {
                report($e);
            }

            return;
        }

        /**
         *
         * @return array
         * @throws Exception
         *
     * @api
     */
        public function getProjectStatusReport()
        {

            $projectStatus = $this->projectRepository->getAll();

            $statusList = ["green" => 0, "yellow" => 0, "red" => 0, "none" => 0];
            foreach ($projectStatus as $project) {
                if (isset($statusList[$project["status"]])) {
                    $statusList[$project["status"]]++;
                } else {
                    $statusList["none"]++;
                }
            }

            return $statusList;
        }

        public function generateTicketReactionsReport()
        {
            $reactionsRepo = app()->make(Reactions::class);
            $collectedReactions = $reactionsRepo->getReactionsByModule("ticketSentiment");

            $reactions = array(
                "🤬" => 0,
                "🤢" => 0,
                "🙁" => 0,
                "😐" => 0,
                "🙂" => 0,
                "😍" => 0,
                "🦄" => 0,
                "other" => 0,
            );

            foreach ($collectedReactions as $reaction) {
                if (isset($reactions[$reaction["reaction"]])) {
                    $reactions[$reaction["reaction"]] = $reactions[$reaction["reaction"]] + $reaction["reactionCount"];
                }
            }

            return $reactions;
        }
    }

}
