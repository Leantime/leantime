<?php

namespace Leantime\Domain\Worktracker\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Worktracker\Services\WorkTracker as WorkTrackerService;

/**
 * HTMX controller that powers the navbar timer widget.
 *
 * Routes:
 *   GET /hx/worktracker/timer/get-status → getStatus()
 *
 * NOTE: start/stop happen via the REST API (POST/PATCH /api/worktracker)
 * directly from the JS in the timer partial. Keeping all session-lifecycle
 * logic on one endpoint avoids the consistency bug we hit during QA where
 * the HX path required a screenshot but the REST path did not.
 */
class Timer extends HtmxController
{
    protected static string $view = 'worktracker::partials.timer';

    private WorkTrackerService $workTrackerService;

    public function init(WorkTrackerService $workTrackerService): void
    {
        $this->workTrackerService = $workTrackerService;
    }

    /**
     * Render the current timer state into the navbar partial.
     * Polled by HTMX every 30 s when a session is running, or fired by the
     * `workTrackerUpdate` body-event right after Start/Stop/Cancel.
     */
    public function getStatus(): void
    {
        if (! session()->exists('userdata')) {
            // No auth — render nothing; the partial gates on the role check too.
            $this->tpl->assign('timerStatus', ['running' => false]);
            $this->tpl->assign('formattedTime', '00:00:00');

            return;
        }

        $userId = (int) session('userdata.id');
        $status = $this->workTrackerService->getTimerStatus($userId);

        $this->tpl->assign('timerStatus', $status);
        $this->tpl->assign('formattedTime', $status['running']
            ? WorkTrackerService::formatDuration($status['elapsed_seconds'])
            : '00:00:00');
    }
}
