<?php

namespace Leantime\Domain\Widgets\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Reports\Services\Reports as ReportService;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Users\Services\Users;
use Leantime\Domain\Widgets\Models\Widget;

class Widgets
{
    use DispatchesEvents;

    /**
     * @api
     */
    public array $availableWidgets = [];

    /**
     * @api
     */
    public array $defaultWidgets = [];

    /**
     * @api
     */
    public Setting $settingRepo;

    private const WIDGET_HISTORY_KEY = 'usersettings.%d.widgetHistory';

    private const ACTIVE_WIDGETS_KEY = 'usersettings.%d.dashboardGrid';

    /**
     * __construct method.
     *
     * Initializes the object and sets the available widgets and default widgets.
     *
     * @param  Setting  $settingRepo  The Setting repository object
     * @return void
     */
    public function __construct(
        Setting $settingRepo,
        protected ProjectService $projectService,
        protected ReportService $reportService
    ) {

        $this->settingRepo = $settingRepo;

        $this->availableWidgets['welcome'] = app()->make("Leantime\Domain\Widgets\Models\Widget", [
            'id' => 'welcome',
            'name' => 'widgets.title.welcome',
            'description' => 'widgets.descriptions.welcome',
            'widgetUrl' => BASE_URL.'/widgets/welcome/get',
            'gridHeight' => 7,
            'gridWidth' => 12,
            'gridMinHeight' => 7,
            'gridMinWidth' => 6,
            'gridX' => 0,
            'gridY' => 0,
            'widgetBackground' => '',
            'widgetTrigger' => 'load, every 5m',
            'alwaysVisible' => true,
            'noTitle' => true,
            'fixed' => true,
        ]);

        $this->availableWidgets['todos'] = app()->make("Leantime\Domain\Widgets\Models\Widget", [
            'id' => 'todos',
            'name' => 'widgets.title.my_todos',
            'description' => 'widgets.descriptions.my_todos',
            'widgetUrl' => BASE_URL.'/widgets/myToDos/get',
            'gridHeight' => 30,
            'gridWidth' => 8,
            'gridMinHeight' => 16,
            'gridMinWidth' => 3,
            'gridX' => 0,
            'gridY' => 7,
            'alwaysVisible' => false,
            'noTitle' => false,
            'fixed' => false,

        ]);

        $this->availableWidgets['calendar'] = app()->make("Leantime\Domain\Widgets\Models\Widget", [
            'id' => 'calendar',
            'name' => 'widgets.title.calendar',
            'description' => 'widgets.descriptions.calendar',
            'gridHeight' => 30,
            'gridWidth' => 4,
            'gridMinHeight' => 12,
            'gridMinWidth' => 3,
            'gridX' => 8,
            'gridY' => 7,
            'alwaysVisible' => false,
            'noTitle' => false,
            'widgetUrl' => BASE_URL.'/widgets/calendar/get',
            'fixed' => false,
        ]);

        $this->availableWidgets['myprojects'] = app()->make("Leantime\Domain\Widgets\Models\Widget", [
            'id' => 'myprojects',
            'name' => 'widgets.title.my_projects',
            'description' => 'widgets.descriptions.my_projects',
            'gridHeight' => 22,
            'gridWidth' => 8,
            'gridMinHeight' => 10,
            'gridMinWidth' => 2,
            'gridX' => 0,
            'gridY' => 43,
            'alwaysVisible' => false,
            'noTitle' => false,
            'widgetUrl' => BASE_URL.'/widgets/myProjects/get',
            'fixed' => false,
        ]);

        $this->defaultWidgets = [
            'welcome' => $this->availableWidgets['welcome'],
            'calendar' => $this->availableWidgets['calendar'],
            'todos' => $this->availableWidgets['todos'],
        ];

        $this->availableWidgets = self::dispatch_filter('availableWidgets', $this->availableWidgets);
        $this->defaultWidgets = self::dispatch_filter('defaultWidgets', $this->defaultWidgets, ['availableWidgets' => $this->availableWidgets]);
    }

    /**
     * Register a new widget.
     *
     * @param  Widget  $widget  The widget to register.
     *
     * @api
     */
    public function registerWidget(Widget $widget): void
    {
        $this->availableWidgets[$widget->id] = $widget;

        // Update widget history for all users
        $users = app()->make(Users::class)->getAll(true);
        foreach ($users as $user) {
            $widgetHistory = $this->getWidgetHistory($user['id']);
            // Don't add to history during registration - let users discover it
            if (! isset($widgetHistory[$widget->id])) {
                $widget->isNew = true;

                continue;
            }
        }
    }

    /**
     * Retrieves all available widgets.
     *
     * @return array The array of available widgets.
     *
     * @api
     */
    public function getAll(): array
    {
        return DispatchesEvents::dispatch_filter('availableWidgets', $this->availableWidgets);
    }

    /**
     * Retrieves the active widgets for a specific user.
     *
     * @param  int  $userId  The ID of the user.
     * @return array The array of active widgets.
     *
     * @api
     */
    public function getActiveWidgets(int $userId): array
    {

        $activeWidgetKey = sprintf(self::ACTIVE_WIDGETS_KEY, $userId);

        if (Cache::has($activeWidgetKey) && is_array(Cache::get($activeWidgetKey)) && count(Cache::get($activeWidgetKey)) > 0) {
            return Cache::get($activeWidgetKey);
        }

        $activeWidgets = $this->settingRepo->getSetting($activeWidgetKey);
        $widgetHistory = $this->getWidgetHistory($userId);
        $widgets = $this->defaultWidgets;

        if ($activeWidgets && $activeWidgets != '') {
            $unserializedData = safe_unserialize($activeWidgets, []);

            $widgets = [];
            foreach ($unserializedData as $key => $widget) {

                if (isset($widget['id']) === false) {
                    continue;
                }

                // Check if this widget exists in available widgets but not in user's stored widgets
                if (isset($this->availableWidgets[$widget['id']]) && ! isset($widgetHistory[$widget['id']])) {
                    $widget['isNew'] = true;
                }

                if (isset($this->availableWidgets[$widget['id']])) {
                    $widget['name'] = $this->availableWidgets[$widget['id']]->name;
                    $widget['widgetUrl'] = $this->availableWidgets[$widget['id']]->widgetUrl;
                    $widget['widgetBackground'] = $this->availableWidgets[$widget['id']]->widgetBackground;
                    $widget['description'] = $this->availableWidgets[$widget['id']]->description;
                    $widget['widgetTrigger'] = $this->availableWidgets[$widget['id']]->widgetTrigger;
                    $widget['alwaysVisible'] = $this->availableWidgets[$widget['id']]->alwaysVisible;
                    $widget['gridMinWidth'] = $this->availableWidgets[$widget['id']]->gridMinWidth;
                    $widget['gridMinHeight'] = $this->availableWidgets[$widget['id']]->gridMinHeight;
                    $widget['noTitle'] = $this->availableWidgets[$widget['id']]->noTitle;
                    $widget['fixed'] = $this->availableWidgets[$widget['id']]->fixed;
                    $widgets[$widget['id']] = app()->make(Widget::class, $widget);
                }
            }
        }

        // Sort Widgets
        $widgets = array_sort($widgets, [['gridY', 'asc'], ['gridX', 'asc']]);

        Cache::set($activeWidgetKey, $widgets, CarbonImmutable::now()->addDays(30));

        return $widgets;
    }

    /**
     * Resets the dashboard grid for a specific user.
     *
     * @param  int  $userId  The ID of the user for whom the dashboard grid needs to be reset.
     *
     * @api
     */
    public function resetDashboard(int $userId): void
    {

        $activeWidgetKey = sprintf(self::ACTIVE_WIDGETS_KEY, $userId);

        Cache::forget($activeWidgetKey);
        $this->settingRepo->deleteSetting($activeWidgetKey);
    }

    /**
     * Get new widgets for the user.
     *
     * @param  int  $userId  The ID of the user.
     * @return array An array of new widgets.
     *
     * @api
     */
    public function getNewWidgets(int $userId): array
    {
        $availableWidgets = $this->getAll();
        $widgetHistory = $this->getWidgetHistory($userId);
        $activeWidgets = $this->getActiveWidgets($userId);

        $newWidgets = [];

        foreach ($availableWidgets as $widgetId => $widget) {
            if (! isset($widgetHistory[$widgetId]) && ! isset($activeWidgets[$widgetId])) {
                $widget->isNew = true;
                $newWidgets[$widgetId] = $widget;
            }
        }

        return $newWidgets;
    }

    /**
     * Get widget history for a user
     */
    private function getWidgetHistory(int $userId): array
    {
        $historyKey = sprintf(self::WIDGET_HISTORY_KEY, $userId);
        $history = $this->settingRepo->getSetting($historyKey);

        return $history ? safe_unserialize($history, []) : [];
    }

    /**
     * Mark a widget as seen by a user
     */
    public function markWidgetAsSeen(int $userId, string $widgetId): void
    {
        $historyKey = sprintf(self::WIDGET_HISTORY_KEY, $userId);
        $history = $this->getWidgetHistory($userId);
        $history[$widgetId] = time();
        $this->settingRepo->saveSetting($historyKey, serialize($history));
    }

    public function saveGrid($data, $userId)
    {
        $activeWidgetKey = sprintf(self::ACTIVE_WIDGETS_KEY, $userId);
        Cache::forget($activeWidgetKey);
        $this->settingRepo->saveSetting($activeWidgetKey,
            serialize($data)
        );

    }

    /**
     * Persists the dashboard grid for a user and, when visibility data marks the
     * widget as visible, records it in the user's widget history.
     *
     * @param  mixed  $data  The grid layout data to persist.
     * @param  int  $userId  The user whose grid is being saved.
     * @param  array|null  $visibilityData  Optional ['visible' => bool, 'widgetId' => string] payload.
     *
     * @api
     */
    public function saveGridForUser($data, int $userId, ?array $visibilityData = null): void
    {
        $this->saveGrid($data, $userId);

        if ($visibilityData !== null && ! empty($visibilityData['visible'])) {
            $this->markWidgetAsSeen($userId, $visibilityData['widgetId']);
        }
    }

    /**
     * Builds the data for the "My Projects" dashboard widget: the open projects
     * assigned to the user, each enriched with its progress and realtime report,
     * plus a deduplicated client map.
     *
     * @param  int  $userId  The user whose assigned projects to load
     * @param  string  $clientFilter  Optional client id to filter projects by ('' = all clients)
     * @return array{projects: array<int, array<string, mixed>>, clients: array<int|string, string>}
     *
     * @api
     */
    public function getMyProjectsWidgetData(int $userId, string $clientFilter = ''): array
    {
        $assignedProjects = $this->projectService->getProjectsAssignedToUser($userId, 'open');

        $clients = [];
        $projects = [];

        if (! is_array($assignedProjects)) {
            return ['projects' => $projects, 'clients' => $clients];
        }

        foreach ($assignedProjects as $project) {
            // Build the client map from every assigned project, regardless of filter.
            if (! array_key_exists($project['clientId'], $clients)) {
                $clients[$project['clientId']] = $project['clientName'];
            }

            if ($clientFilter !== '' && $project['clientId'] != $clientFilter) {
                continue;
            }

            $project['progress'] = $this->projectService->getProjectProgress($project['id']);
            $project['report'] = $this->reportService->getRealtimeReport($project['id'], '');

            $projects[] = $project;
        }

        return ['projects' => $projects, 'clients' => $clients];
    }
}
