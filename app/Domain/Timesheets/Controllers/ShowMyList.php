<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Carbon\CarbonInterface;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class ShowMyList extends Controller
{
    private TimesheetService $timesheetService;

    /**
     * @param TimesheetService $timesheetService
     *
     * @return void
     */
    public function init(TimesheetService $timesheetService): void
    {
        $this->timesheetService = $timesheetService;
        session(["lastPage" => BASE_URL . "/timesheets/showMyList"]);
    }

    /**
     * run - display template and edit data
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function run(): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

        $kind = 'all';
        if (!empty($_POST['kind'])) {
            $kind = ($_POST['kind']);
        }

        // Use UTC here as all data stored in the database should be UTC (start in user's timezone and convert to UTC).
        // The front end javascript is hardcode to start the week on mondays, so we use that here too.

        //Get start of the week in current users timezone and then switch to UTC
        $dateFrom = dtHelper()->userNow()->startOfWeek(CarbonInterface::MONDAY)->setToDbTimezone();
        $dateTo = dtHelper()->userNow()->endOfWeek()->setToDbTimezone();

        if (!empty($_POST['dateFrom'])) {
            $dateFrom = dtHelper()->parseUserDateTime($_POST['dateFrom'])->setToDbTimezone();
        }

        if (!empty($_POST['dateTo'])) {
            $dateTo = dtHelper()->parseUserDateTime($_POST['dateTo'])->setToDbTimezone();
        }

        $this->tpl->assign('dateFrom', $dateFrom);
        $this->tpl->assign('dateTo', $dateTo);
        $this->tpl->assign('actKind', $kind);
        $this->tpl->assign('kind', $this->timesheetService->getLoggableHourTypes());
        $this->tpl->assign('allTimesheets', $this->timesheetService->getAll(
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            projectId: -1,
            kind: $kind,
            userId: session("userdata.id"),
            invEmpl: 0,
            invComp: 0,
            paid: 0
        ));

        return $this->tpl->display('timesheets.showMyList');
    }
}
