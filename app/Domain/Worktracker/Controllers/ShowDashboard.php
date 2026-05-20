<?php

namespace Leantime\Domain\Worktracker\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Worktracker\Services\WorkTracker as WorkTrackerService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Employee work-session dashboard.
 * URL: /worktracker/showDashboard
 */
class ShowDashboard extends Controller
{
    private WorkTrackerService $workTrackerService;

    public function init(WorkTrackerService $workTrackerService): void
    {
        $this->workTrackerService = $workTrackerService;
    }

    public function run(): Response
    {
        Auth::authOrRedirect([Roles::$editor, Roles::$manager, Roles::$admin, Roles::$owner], true);

        $userId    = (int) session('userdata.id');
        $dashboard = $this->workTrackerService->getEmployeeDashboard($userId);

        $sessions = array_map(function ($row) {
            $row = (array) $row;
            $row['duration_formatted'] = isset($row['total_duration'])
                ? WorkTrackerService::formatDuration((int) $row['total_duration'])
                : '—';
            $row['start_screenshot_url'] = $this->workTrackerService->screenshotUrl(
                (int) ($row['id'] ?? 0),
                'start',
                $row['start_screenshot'] ?? ''
            );
            $row['end_screenshot_url'] = $this->workTrackerService->screenshotUrl(
                (int) ($row['id'] ?? 0),
                'end',
                $row['end_screenshot'] ?? ''
            );

            return $row;
        }, $dashboard['sessions']);

        $this->tpl->assign('activeSession', $dashboard['active_session']);
        $this->tpl->assign('elapsedSeconds', $dashboard['elapsed_seconds']);
        $this->tpl->assign('elapsedFormatted', WorkTrackerService::formatDuration($dashboard['elapsed_seconds']));
        $this->tpl->assign('todayTotal', WorkTrackerService::formatDuration($dashboard['today_total']));
        $this->tpl->assign('weekTotal', WorkTrackerService::formatDuration($dashboard['week_total']));
        $this->tpl->assign('sessions', $sessions);
        $this->tpl->assign('totalCount', $dashboard['total_count']);

        return $this->tpl->display('worktracker.showDashboard');
    }
}
