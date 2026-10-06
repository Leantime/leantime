<?php

namespace Leantime\Domain\Reports\Services;

use Carbon\CarbonImmutable;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Configuration\AppSettings as AppSettingCore;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Domains\BaseService;
use Leantime\Domain\Blueprints\Repositories\Blueprints as BlueprintsRepository;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
use Leantime\Domain\Plugins\Repositories\Plugins as PluginRepository;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Reactions\Repositories\Reactions;
use Leantime\Domain\Reports\Permissions\ReportsPermissions;
use Leantime\Domain\Reports\Repositories\Reports as ReportRepository;
use Leantime\Domain\Setting\Services\Setting as SettingsService;
use Leantime\Domain\Sprints\Repositories\Sprints as SprintRepository;
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

/**
 * Reports service: per-project report aggregation (burndown, ticket-status history) plus the
 * system-level daily-ingestion and telemetry tasks.
 *
 * Authorization model: the three by-projectId @api reads carry a dispatch
 * #[RequiresPermission(reports.view, projectIdParam: 'projectId')] gate, authorized against the
 * REQUESTED project (closes the cross-project RPC IDOR). The system/cron methods (ingestion,
 * telemetry) are NOT @api — they run from the scheduler with no session user and must never be
 * RPC-reachable.
 *
 * @api
 */
class Reports extends BaseService
{
    private AppSettingCore $appSettings;

    private EnvironmentCore $config;

    private ProjectRepository $projectRepository;

    private SprintRepository $sprintRepository;

    private ReportRepository $reportRepository;

    private SettingsService $settings;

    private TicketRepository $ticketRepository;

    private SprintService $sprintService;

    public function __construct(
        AppSettingCore $appSettings,
        EnvironmentCore $config,
        ProjectRepository $projectRepository,
        SprintRepository $sprintRepository,
        ReportRepository $reportRepository,
        SettingsService $settings,
        TicketRepository $ticketRepository,
        SprintService $sprintService
    ) {
        $this->appSettings = $appSettings;
        $this->config = $config;
        $this->projectRepository = $projectRepository;
        $this->sprintRepository = $sprintRepository;
        $this->reportRepository = $reportRepository;
        $this->settings = $settings;
        $this->ticketRepository = $ticketRepository;
        $this->sprintService = $sprintService;
    }

    /**
     * Resolves which sprint burndown to display on the reports page.
     *
     * Mirrors the legacy controller selection order exactly:
     * 1. An explicitly requested sprint id (from the query string).
     * 2. Otherwise the project's current sprint.
     * 3. Otherwise the first available sprint.
     *
     * The returned 'currentSprintId' preserves the original behaviour:
     * when a sprint id is explicitly requested it is echoed back as-is
     * (even if the sprint cannot be loaded); when falling back to the
     * current/first sprint the resolved sprint object's id is used.
     * When the project has no sprints at all, both values are false.
     *
     * @param  int  $projectId  Project to resolve the burndown for.
     * @param  int|null  $requestedSprintId  Sprint id explicitly requested by the user, or null.
     * @return array{chart: false|array, currentSprintId: int|false} Burndown chart data and the resolved sprint id.
     *
     * @api
     */
    #[RequiresPermission(ReportsPermissions::VIEW, projectIdParam: 'projectId')]
    public function getSprintBurndownForReport(int $projectId, ?int $requestedSprintId): array
    {
        $allSprints = $this->sprintService->getAllSprints($projectId);

        if (count($allSprints) === 0) {
            return ['chart' => false, 'currentSprintId' => false];
        }

        $sprintChart = false;

        if ($requestedSprintId !== null) {
            $sprintObject = $this->sprintService->getSprint($requestedSprintId);
            if ($sprintObject) {
                $sprintChart = $this->sprintService->getSprintBurndown($sprintObject);
            }

            return ['chart' => $sprintChart, 'currentSprintId' => $requestedSprintId];
        }

        $currentSprint = $this->sprintService->getCurrentSprintId($projectId);

        if ($currentSprint !== false) {
            $sprintObject = $this->sprintService->getSprint($currentSprint);
            if ($sprintObject) {
                $sprintChart = $this->sprintService->getSprintBurndown($sprintObject);

                return ['chart' => $sprintChart, 'currentSprintId' => $sprintObject->id];
            }

            return ['chart' => $sprintChart, 'currentSprintId' => false];
        }

        $sprintChart = $this->sprintService->getSprintBurndown($allSprints[0]);

        return ['chart' => $sprintChart, 'currentSprintId' => $allSprints[0]->id];
    }

    /**
     * Runs the daily report ingestion for the session's current project.
     *
     * Not @api: an internal web-path helper (called by the gated Reports\Controllers\Show after
     * dispatch enforcement). It reads session('currentProject'), so an RPC caller would have no
     * meaningful project binding — and it was needlessly RPC-exposed before.
     *
     * @throws BindingResolutionException
     */
    public function dailyIngestion(): void
    {
        $this->runIngestionForProject(session('currentProject'));
    }

    protected function runIngestionForProject(int $projectId): void
    {

        if (Cache::has('dailyReports-'.$projectId) === false || Cache::get('dailyReports-'.$projectId) < dtHelper()->dbNow()->endOfDay()) {

            // Check if the dailyingestion cycle was executed already. There should be one entry for backlog and one entry for current sprint (unless there is no current sprint
            // Get current Sprint Id, if no sprint available, dont run the sprint burndown

            $lastEntries = $this->reportRepository->checkLastReportEntries($projectId);

            // If we receive 2 entries we have a report already. If we have one entry then we ran the backlog one and that means there was no current sprint.
            if (count($lastEntries) == 0) {
                $currentSprint = $this->sprintRepository->getCurrentSprint($projectId);

                if ($currentSprint !== false) {
                    $sprintReport = $this->reportRepository->runTicketReport($projectId, $currentSprint->id);
                    if ($sprintReport !== false) {
                        $this->reportRepository->addReport($sprintReport);
                    }
                }

                $backlogReport = $this->reportRepository->runTicketReport($projectId, '');

                if ($backlogReport !== false) {

                    $this->reportRepository->addReport($backlogReport);

                    Cache::put('dailyReports-'.$projectId, dtHelper()->dbNow()->endOfDay(), 14400); // 4hours

                }
            }

        }
    }

    public function cronDailyIngestion(): void
    {
        $projects = $this->projectRepository->getAll();

        foreach ($projects as $project) {
            $this->runIngestionForProject($project['id']);
        }

    }

    /**
     * Returns a project's stored report history, authorized against the requested project.
     *
     * $projectId is typed int so the param is REQUIRED and non-null: a JSON-RPC caller cannot
     * pass null to make PermissionEnforcer::resolveProjectId() fall back to the session project
     * (its isset() check treats explicit null as absent), which would dodge the per-target gate.
     *
     * @api
     */
    #[RequiresPermission(ReportsPermissions::VIEW, projectIdParam: 'projectId')]
    public function getFullReport(int $projectId): false|array
    {
        return $this->reportRepository->getFullReport($projectId);
    }

    /**
     * Computes a project's current ticket report, authorized against the requested project.
     * The repository scopes by BOTH projectId and sprint, so a foreign sprint id yields no rows.
     *
     * $projectId is typed int for the same reason as getFullReport() — it keeps the dispatch gate
     * bound to the requested project (no null → session fallback). $sprintId stays mixed because
     * the empty string is the meaningful "backlog" selector.
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    #[RequiresPermission(ReportsPermissions::VIEW, projectIdParam: 'projectId')]
    public function getRealtimeReport(int $projectId, $sprintId): array|bool
    {
        return $this->reportRepository->runTicketReport($projectId, $sprintId);
    }

    /**
     * Builds the anonymous telemetry payload (instance-wide usage counts + company GUID).
     *
     * Not @api: system-level data source for sendAnonymousTelemetry() only. Exposing it over
     * JSON-RPC let any authenticated user read instance-wide aggregates (user/project/feature
     * counts) — reconnaissance, not a user capability.
     */
    public function getAnonymousTelemetry(
        IdeaRepository $ideaRepository,
        UserRepository $userRepository,
        ClientRepository $clientRepository,
        CommentRepository $commentsRepository,
        TimesheetRepository $timesheetRepo,
        BlueprintsRepository $blueprintsRepo,
        PluginRepository $pluginRepository
    ): array {

        // Get anonymous company guid
        $companyId = $this->settings->getCompanyId();

        self::dispatch_event('beforeTelemetrySend', ['companyId' => $companyId]);

        $companyLang = $this->settings->getSetting('companysettings.language');
        if ($companyLang != '') {
            $currentLanguage = $companyLang;
        } else {
            $currentLanguage = $this->config->language;
        }

        $projectStatusCount = $this->getProjectStatusReport();

        $taskSentiment = $this->generateTicketReactionsReport();

        $telemetry = [
            'date' => '',
            'companyId' => $companyId,
            'env' => 'oss',
            'version' => $this->appSettings->appVersion,
            'language' => $currentLanguage,
            'numUsers' => $userRepository->getNumberOfUsers(),
            // Day precision only: the exact timestamp of the last login is more than we need.
            'lastUserLogin' => substr((string) $userRepository->getLastLogin(), 0, 10),
            'activeUsers7d' => $userRepository->countActiveUsersSince(CarbonImmutable::now('UTC')->subDays(7)),
            'activeUsers30d' => $userRepository->countActiveUsersSince(CarbonImmutable::now('UTC')->subDays(30)),

            'numProjects' => $this->projectRepository->getNumberOfProjects(null, 'project'),
            'numProjectsGreen' => $projectStatusCount['green'] ?? 0,
            'numProjectsYellow' => $projectStatusCount['yellow'] ?? 0,
            'numProjectsRed' => $projectStatusCount['red'] ?? 0,
            'numProjectsNone' => $projectStatusCount['none'] ?? 0,

            'numStrategies' => $this->projectRepository->getNumberOfProjects(null, 'strategy'),
            'numPrograms' => $this->projectRepository->getNumberOfProjects(null, 'program'),
            'numClients' => $clientRepository->getNumberOfClients(),
            'numComments' => $commentsRepository->countComments(),
            'numMilestones' => $this->ticketRepository->getNumberOfMilestones(),
            'numTickets' => $this->ticketRepository->getNumberOfAllTickets(),

            'numBoards' => $ideaRepository->getNumberOfBoards(),

            'numIdeaItems' => $ideaRepository->getNumberOfIdeas(),
            'numHoursBooked' => $timesheetRepo->getHoursBooked(),

            'numResearchBoards' => $blueprintsRepo->getNumberOfBoards(null, 'leancanvas'),
            'numResearchItems' => $blueprintsRepo->getNumberOfCanvasItems(null, 'leancanvas'),

            'numRetroBoards' => $blueprintsRepo->getNumberOfBoards(null, 'retroscanvas'),
            'numRetroItems' => $blueprintsRepo->getNumberOfCanvasItems(null, 'retroscanvas'),

            'numGoalBoards' => $blueprintsRepo->getNumberOfBoards(null, 'goalcanvas'),
            'numGoalItems' => $blueprintsRepo->getNumberOfCanvasItems(null, 'goalcanvas'),

            'numValueCanvasBoards' => $blueprintsRepo->getNumberOfBoards(null, 'valuecanvas'),
            'numValueCanvasItems' => $blueprintsRepo->getNumberOfCanvasItems(null, 'valuecanvas'),

            'numMinEmpathyBoards' => $blueprintsRepo->getNumberOfBoards(null, 'minempathycanvas'),
            'numMinEmpathyItems' => $blueprintsRepo->getNumberOfCanvasItems(null, 'minempathycanvas'),

            'numOBMBoards' => $blueprintsRepo->getNumberOfBoards(null, 'obmcanvas'),
            'numOBMItems' => $blueprintsRepo->getNumberOfCanvasItems(null, 'obmcanvas'),

            'numSWOTBoards' => $blueprintsRepo->getNumberOfBoards(null, 'swotcanvas'),
            'numSWOTItems' => $blueprintsRepo->getNumberOfCanvasItems(null, 'swotcanvas'),

            'numSBBoards' => $blueprintsRepo->getNumberOfBoards(null, 'sbcanvas'),
            'numSBItems' => $blueprintsRepo->getNumberOfCanvasItems(null, 'sbcanvas'),

            'numRISKSBoards' => $blueprintsRepo->getNumberOfBoards(null, 'riskscanvas'),
            'numRISKSItems' => $blueprintsRepo->getNumberOfCanvasItems(null, 'riskscanvas'),

            'numEABoards' => $blueprintsRepo->getNumberOfBoards(null, 'eacanvas'),
            'numEAItems' => $blueprintsRepo->getNumberOfCanvasItems(null, 'eacanvas'),

            'numINSIGHTSBoards' => $blueprintsRepo->getNumberOfBoards(null, 'insightscanvas'),
            'numINSIGHTSItems' => $blueprintsRepo->getNumberOfCanvasItems(null, 'insightscanvas'),

            'numWikiBoards' => $blueprintsRepo->getNumberOfBoards(null, 'wiki'),
            'numWikiItems' => $blueprintsRepo->getNumberOfCanvasItems(null, 'wiki'),

            'numTaskSentimentAngry' => $taskSentiment['🤬'] ?? 0,
            'numTaskSentimentDisgust' => $taskSentiment['🤢'] ?? 0,
            'numTaskSentimentUnhappy' => $taskSentiment['🙁'] ?? 0,
            'numTaskSentimentNeutral' => $taskSentiment['😐'] ?? 0,
            'numTaskSentimentHappy' => $taskSentiment['🙂'] ?? 0,
            'numTaskSentimentLove' => $taskSentiment['😍'] ?? 0,
            'numTaskSentimenUnicorn' => $taskSentiment['🦄'] ?? 0,

            'serverSoftware' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'isDocker' => $this->isRunningInDocker(),
            'phpSapiName' => php_sapi_name(),
            'phpOs' => PHP_OS,
            'phpVersion' => PHP_VERSION,
            'dbDriver' => (string) config('database.default'),
            'cacheDriver' => (string) config('cache.default'),
            'sessionDriver' => (string) config('session.driver'),
            'queueDriver' => (string) config('queue.default'),
        ] + $this->getEnabledPluginSummary($pluginRepository);

        $telemetry = self::dispatch_filter('beforeReturnTelemetry', $telemetry);

        return $telemetry;
    }

    /**
     * Sends the daily anonymous telemetry ping (throttled to once a day, opt-out respected).
     *
     * Not @api: a system task invoked by the scheduler (register.php cron, no session user) and
     * the dashboard's lazy trigger — never a user-invokable RPC method (it makes an outbound
     * HTTP request).
     *
     * @throws BindingResolutionException
     */
    public function sendAnonymousTelemetry(): bool|PromiseInterface
    {
        if (! $this->isTelemetryEnabled()) {
            return false;
        }

        $today = CarbonImmutable::now('UTC')->format('Y-m-d');
        $lastUpdate = $this->settings->getSetting('companysettings.telemetry.lastUpdate');

        if ($lastUpdate == $today) {
            return false;
        }

        $telemetry = app()->call([$this, 'getAnonymousTelemetry']);
        $telemetry['date'] = $today;

        try {
            return $this->postTelemetry($telemetry)->then(function () use ($today) {
                $this->settings->saveSetting('companysettings.telemetry.lastUpdate', $today);
            });
        } catch (\Exception $e) {
            Log::error($e);

            return false;
        }
    }

    /**
     * Whether this instance may send telemetry.
     *
     * Two switches, both must allow it: the LEAN_ALLOW_TELEMETRY config flag and the admin's
     * company-settings toggle (companysettings.telemetry.optOut). The older
     * companysettings.telemetry.active key is deliberately ignored: while the settings toggle
     * was missing (Nov 2024 – Oct 2026) every company-settings save wrote it to false, so it
     * does not reflect an admin decision.
     *
     * Not @api: system-level check used by sendAnonymousTelemetry() and the settings screen.
     */
    public function isTelemetryEnabled(): bool
    {
        $allowTelemetry = app('config')->allowTelemetry ?? true;
        if ($allowTelemetry !== true) {
            return false;
        }

        return $this->settings->getSetting('companysettings.telemetry.optOut') !== 'true';
    }

    /**
     * Opts the whole instance out of telemetry and tells the telemetry server once.
     *
     * Sends a single opt-out notice (company id, version, optOut flag — no usage data) so the
     * instance can be marked as opted out instead of looking abandoned, then stores the opt-out.
     *
     * Not @api: an instance-wide settings MUTATION. It is invoked internally by the admin-gated
     * company-settings save (Setting::saveCompanySettings) — over JSON-RPC it previously let ANY
     * authenticated user flip the company-wide telemetry setting.
     */
    public function optOutTelemetry(): void
    {
        $this->settings->saveSetting('companysettings.telemetry.optOut', 'true');

        $allowTelemetry = app('config')->allowTelemetry ?? true;
        if ($allowTelemetry !== true) {
            return;
        }

        $optOutNotice = [
            'date' => CarbonImmutable::now('UTC')->format('Y-m-d'),
            'companyId' => $this->settings->getCompanyId(),
            'env' => 'oss',
            'version' => $this->appSettings->appVersion,
            'optOut' => true,
        ];

        try {
            $this->postTelemetry($optOutNotice)->wait();
        } catch (\Exception $e) {
            Log::warning('Could not send telemetry opt-out notice: '.$e->getMessage());
        }
    }

    /**
     * Re-enables telemetry after an admin opted out.
     *
     * Not @api: instance-wide settings mutation, only called from the admin-gated settings save.
     */
    public function optInTelemetry(): void
    {
        $this->settings->saveSetting('companysettings.telemetry.optOut', 'false');
    }

    /**
     * Posts a telemetry payload to the Leantime telemetry server.
     *
     * The payload travels as JSON inside the `telemetry` form field — the contract every
     * released Leantime version uses, so the server accepts old and new clients alike.
     */
    private function postTelemetry(array $payload): PromiseInterface
    {
        $httpClient = new Client;

        return $httpClient->postAsync('https://telemetry.leantime.io', [
            'form_params' => [
                'telemetry' => json_encode($payload),
            ],
            // Short connect timeout so an offline/air-gapped server (or a
            // CI runner with no egress) fails fast instead of blocking the
            // dashboard's Welcome widget — and saturating PHP-FPM workers —
            // for minutes. The previous 480s total timeout hung the page
            // when telemetry was unreachable. (#3372/#3373)
            'connect_timeout' => 2,
            'timeout' => 5,
        ]);
    }

    /**
     * Enabled plugins, privacy-safe: names of marketplace plugins only (published package
     * names, phar format) plus a count of locally installed folder plugins, whose directory
     * names are chosen locally and may identify the organisation.
     *
     * Reads the repository directly: telemetry runs from cron without a session user, so the
     * permission-gated Plugins service is not usable here. System plugins from LEAN_PLUGINS
     * are counted as folder plugins.
     *
     * @return array{plugins: array<int, string>, numFolderPlugins: int}
     */
    private function getEnabledPluginSummary(PluginRepository $pluginRepository): array
    {
        $marketplacePlugins = [];
        $folderPlugins = [];

        try {
            $enabledPlugins = $pluginRepository->getAllPlugins(true);
        } catch (\Exception $e) {
            $enabledPlugins = [];
        }

        foreach (is_array($enabledPlugins) ? $enabledPlugins : [] as $plugin) {
            if (($plugin->format ?? '') === 'phar') {
                $marketplacePlugins[] = (string) $plugin->foldername;
            } else {
                $folderPlugins[] = strtolower((string) $plugin->foldername);
            }
        }

        $systemPlugins = array_filter(array_map('trim', explode(',', (string) ($this->config->plugins ?? ''))));
        foreach ($systemPlugins as $systemPlugin) {
            $folderPlugins[] = strtolower($systemPlugin);
        }

        sort($marketplacePlugins);

        return [
            'plugins' => array_values(array_unique($marketplacePlugins)),
            'numFolderPlugins' => count(array_unique($folderPlugins)),
        ];
    }

    /**
     * Counts ALL projects in the instance by status color — a telemetry data source.
     *
     * Not @api: instance-wide aggregation with no project scope; over JSON-RPC it disclosed the
     * whole company's project-health summary to any authenticated user.
     *
     * @return array
     *
     * @throws Exception
     */
    public function getProjectStatusReport()
    {

        $projectStatus = $this->projectRepository->getAll();

        $statusList = ['green' => 0, 'yellow' => 0, 'red' => 0, 'none' => 0];
        foreach ($projectStatus as $project) {
            if (isset($statusList[$project['status']])) {
                $statusList[$project['status']]++;
            } else {
                $statusList['none']++;
            }
        }

        return $statusList;
    }

    public function generateTicketReactionsReport()
    {
        $reactionsRepo = app()->make(Reactions::class);
        $collectedReactions = $reactionsRepo->getReactionsByModule('ticketSentiment');

        $reactions = [
            '🤬' => 0,
            '🤢' => 0,
            '🙁' => 0,
            '😐' => 0,
            '🙂' => 0,
            '😍' => 0,
            '🦄' => 0,
            'other' => 0,
        ];

        foreach ($collectedReactions as $reaction) {
            if (isset($reactions[$reaction['reaction']])) {
                $reactions[$reaction['reaction']] = $reactions[$reaction['reaction']] + $reaction['reactionCount'];
            }
        }

        return $reactions;
    }

    /**
     * Checks if Leantime is running in a Docker environment
     * Uses multiple detection methods and handles errors gracefully
     */
    private function isRunningInDocker(): bool
    {
        // Method 1: Check for /.dockerenv file
        try {
            if (is_file('/.dockerenv')) {
                return true;
            }
        } catch (\Exception $e) {
            // Silently fail if file access is restricted
        }

        // Method 2: Check for Docker-specific environment variables
        if (getenv('DOCKER_CONTAINER') !== false || getenv('IS_DOCKER') !== false) {
            return true;
        }

        // Method 3: Check cgroup info (works on Linux hosts)
        try {
            return strpos(file_get_contents('/proc/1/cgroup'), 'docker') !== false;
        } catch (\Exception $e) {
            return false; // Return false if all detection methods fail
        }
    }
}
