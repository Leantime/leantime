<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Carbon\CarbonInterface;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Timesheets\Permissions\TimesheetsPermissions;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Symfony\Component\HttpFoundation\Response;

class ShowMyList extends Controller
{
    private TimesheetService $timesheetService;

    /**
     * Initializes dependencies.
     */
    public function init(TimesheetService $timesheetService): void
    {
        $this->timesheetService = $timesheetService;
        session(['lastPage' => BASE_URL.'/timesheets/showMyList']);
    }

    /**
     * Displays the user's timesheet list.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(TimesheetsPermissions::VIEW, global: true)]
    public function get(array $params): Response
    {
        $kind = 'all';
        $dateFrom = dtHelper()->userNow()->startOfWeek(CarbonInterface::MONDAY)->setToDbTimezone();
        $dateTo = dtHelper()->userNow()->endOfWeek()->setToDbTimezone();

        $this->assignTemplateVars($dateFrom, $dateTo, $kind);

        return $this->tpl->display('timesheets.showMyList');
    }

    /**
     * Handles timesheet list filter changes.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(TimesheetsPermissions::VIEW, global: true)]
    public function post(array $params): Response
    {
        $kind = ! empty($_POST['kind']) ? $_POST['kind'] : 'all';

        $dateFrom = dtHelper()->userNow()->startOfWeek(CarbonInterface::MONDAY)->setToDbTimezone();
        $dateTo = dtHelper()->userNow()->endOfWeek()->setToDbTimezone();

        if (! empty($_POST['dateFrom'])) {
            $dateFrom = dtHelper()->parseUserDateTime($_POST['dateFrom'])->setToDbTimezone();
        }

        if (! empty($_POST['dateTo'])) {
            $dateTo = dtHelper()->parseUserDateTime($_POST['dateTo'])->setToDbTimezone();
        }

        $this->assignTemplateVars($dateFrom, $dateTo, $kind);

        return $this->tpl->display('timesheets.showMyList');
    }

    /**
     * Assigns common template variables.
     */
    private function assignTemplateVars(CarbonInterface $dateFrom, CarbonInterface $dateTo, string $kind): void
    {
        $this->tpl->assign('dateFrom', $dateFrom);
        $this->tpl->assign('dateTo', $dateTo);
        $this->tpl->assign('actKind', $kind);
        $this->tpl->assign('kind', $this->timesheetService->getLoggableHourTypes());
        $this->tpl->assign('allTimesheets', $this->timesheetService->getAll(
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            projectId: -1,
            kind: $kind,
            userId: session('userdata.id'),
            invEmpl: '-1',
            invComp: '-1',
            paid: '-1'
        ));
    }
}
