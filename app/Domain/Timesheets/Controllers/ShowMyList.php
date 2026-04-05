<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for the "My Timesheets" read-only list view with DataTables.
 */
class ShowMyList extends Controller
{
    private TimesheetService $timesheetService;

    /**
     * Initialise controller dependencies.
     */
    public function init(
        TimesheetService $timesheetService,
    ): void {
        $this->timesheetService = $timesheetService;

        session(['lastPage' => BASE_URL.'/timesheets/showMyList']);
    }

    /**
     * Display the list view.
     *
     * @throws \Exception
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

        $kind = session('timesheetListKind', 'all');

        $dateFrom = dtHelper()->userNow()->startOfWeek(CarbonInterface::MONDAY)->setToDbTimezone();
        $dateTo = dtHelper()->userNow()->endOfWeek()->setToDbTimezone();

        if (session()->has('timesheetListDateFrom')) {
            $dateFrom = session('timesheetListDateFrom');
        }

        if (session()->has('timesheetListDateTo')) {
            $dateTo = session('timesheetListDateTo');
        }

        $this->assignTemplateVars($dateFrom, $dateTo, $kind);

        return $this->tpl->display('timesheets.showMyList');
    }

    /**
     * Handle filter form submissions.
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

        $kind = $_POST['kind'] ?? 'all';

        $dateFrom = dtHelper()->userNow()->startOfWeek(CarbonInterface::MONDAY)->setToDbTimezone();
        $dateTo = dtHelper()->userNow()->endOfWeek()->setToDbTimezone();

        if (! empty($_POST['dateFrom'])) {
            try {
                $dateFrom = dtHelper()->parseUserDateTime($_POST['dateFrom'])->setToDbTimezone();
            } catch (\Exception $e) {
                Log::warning($e);
                $this->tpl->setNotification('Could not parse date', 'error', 'timesheet_filter');
            }
        }

        if (! empty($_POST['dateTo'])) {
            try {
                $dateTo = dtHelper()->parseUserDateTime($_POST['dateTo'])->setToDbTimezone();
            } catch (\Exception $e) {
                Log::warning($e);
                $this->tpl->setNotification('Could not parse date', 'error', 'timesheet_filter');
            }
        }

        session(['timesheetListDateFrom' => $dateFrom]);
        session(['timesheetListDateTo' => $dateTo]);
        session(['timesheetListKind' => $kind]);

        $this->assignTemplateVars($dateFrom, $dateTo, $kind);

        return $this->tpl->display('timesheets.showMyList');
    }

    /**
     * Assign all template variables needed for the list view.
     */
    private function assignTemplateVars(mixed $dateFrom, mixed $dateTo, string $kind): void
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
            paid: '-1',
        ));
    }
}
