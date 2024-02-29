<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Carbon\Carbon;
use Leantime\Core\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Auth\Services\Auth;
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
        $_SESSION['lastPage'] = BASE_URL . "/timesheets/showMyList";
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

        $dateFrom = Carbon::now('UTC')->startOfMonth();
        if (!empty($_POST['dateFrom'])) {
            $dateFrom = Carbon::createFromFormat($_SESSION['usersettings.language.date_format'], $_POST['dateFrom'], 'UTC');
        }

        $dateTo = Carbon::now('UTC')->endOfMonth();
        if (!empty($_POST['dateTo'])) {
            $dateFrom = Carbon::createFromFormat($_SESSION['usersettings.language.date_format'], $_POST['dateTo'], 'UTC');
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
            userId: $_SESSION['userdata']['id'],
            invEmpl: 0,
            invComp: 0,
            paid: 0
        ));

        return $this->tpl->display('timesheets.showMyList');
    }
}
