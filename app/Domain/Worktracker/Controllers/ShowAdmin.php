<?php

namespace Leantime\Domain\Worktracker\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Worktracker\Services\WorkTracker as WorkTrackerService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin work-session monitor.
 * URL: /worktracker/showAdmin
 * Access: manager, admin, owner only.
 */
class ShowAdmin extends Controller
{
    private WorkTrackerService $workTrackerService;

    public function init(WorkTrackerService $workTrackerService): void
    {
        $this->workTrackerService = $workTrackerService;
    }

    public function run(): Response
    {
        // Spec section 12 — Screenshot Access Rules:
        //   Admin / Owner → Full access
        //   Manager       → Limited access (NOT permitted on the full admin monitor)
        //   Employee      → Own sessions only (uses /worktracker/showDashboard)
        Auth::authOrRedirect([Roles::$admin, Roles::$owner], true);

        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 50;

        $data     = $this->workTrackerService->getAdminDashboard($page, $perPage);
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
            $row['employee_name'] = trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? ''));

            return $row;
        }, $data['sessions']);

        $this->tpl->assign('sessions', $sessions);
        $this->tpl->assign('totalCount', $data['total_count']);
        $this->tpl->assign('page', $data['page']);
        $this->tpl->assign('totalPages', $data['total_pages']);
        $this->tpl->assign('activeNow', $data['active_now']);
        $this->tpl->assign('todayGrandTotal', WorkTrackerService::formatDuration($data['today_grand_total']));

        return $this->tpl->display('worktracker.showAdmin');
    }
}
