<?php

namespace Leantime\Domain\Timesheets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;

/**
 * Lazy-loaded, per-entry audit history popup for a single zp_timesheets row. Manager+ only —
 * enforced entirely by TimesheetService::getTimeEntryHistory()'s #[RequiresPermission]
 * attribute; this controller does no gating of its own, matching how TicketTimeLog delegates
 * authorization to the service layer.
 */
class TimeEntryHistory extends HtmxController
{
    protected static string $view = 'timesheets::partials.timeEntryHistory';

    private TimesheetService $timesheetService;

    public function init(TimesheetService $timesheetService): void
    {
        $this->timesheetService = $timesheetService;
    }

    public function get(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        $this->tpl->assign('timesheetId', $id);
        $this->tpl->assign('history', $id ? $this->timesheetService->getTimeEntryHistory($id) : []);
    }
}
