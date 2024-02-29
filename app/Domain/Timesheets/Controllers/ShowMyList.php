<?php

namespace Leantime\Domain\Timesheets\Controllers;

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
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

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
        $projectFilter =  $_SESSION['currentProject'];
        $dateFrom = mktime(0, 0, 0, date("m"), '1', date("Y"));
        $dateTo = mktime(0, 0, 0, date("m"), date("t"), date("Y"));
        $dateFrom = date("Y-m-d 00:00:00", $dateFrom);
        $dateTo = date("Y-m-d 00:00:00", $dateTo);
        $kind = 'all';

        if (isset($_POST['kind']) && $_POST['kind'] != '') {
            $kind = ($_POST['kind']);
        }

        if (isset($_POST['dateFrom']) && $_POST['dateFrom'] != '') {
            $dateFrom =  format($_POST['dateFrom'])->isoDate();
        }

        if (isset($_POST['dateTo']) && $_POST['dateTo'] != '') {
            $dateTo =  format($_POST['dateTo'])->isoDateEnd();
        }

        $this->tpl->assign('dateFrom', $dateFrom);
        $this->tpl->assign('dateTo', $dateTo);
        $this->tpl->assign('actKind', $kind);
        $this->tpl->assign('kind', $this->timesheetService->getLoggableHourTypes());
        $this->tpl->assign('allTimesheets', $this->timesheetService->getAll(-1, $kind, $dateFrom, $dateTo, $_SESSION['userdata']['id'], 0, 0, "-1", 0));

        return $this->tpl->display('timesheets.showMyList');
    }
}
