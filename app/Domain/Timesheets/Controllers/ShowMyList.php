<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Carbon\CarbonInterface;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Symfony\Component\HttpFoundation\Response;

class ShowMyList extends Controller
{
    private TimesheetService $timesheetService;

    private ProjectService $projectService;

    private ClientService $clientService;

    public function init(TimesheetService $timesheetService, ProjectService $projectService, ClientService $clientService): void
    {
        $this->timesheetService = $timesheetService;
        $this->projectService = $projectService;
        $this->clientService = $clientService;
        session(['lastPage' => BASE_URL.'/timesheets/showMyList']);
    }

    /**
     * run - display template and edit data
     *
     *
     * @throws \Exception
     */
    public function run(): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

        $kind = 'all';
        if (! empty($_POST['kind'])) {
            $kind = (string) $_POST['kind'];
        }

        $projectId = -1;
        if (isset($_POST['projectId']) && $_POST['projectId'] !== '' && $_POST['projectId'] !== 'all') {
            $projectId = (int) $_POST['projectId'];
        }

        $clientId = -1;
        if (isset($_POST['clientId']) && $_POST['clientId'] !== '' && $_POST['clientId'] !== '-1') {
            $clientId = (int) $_POST['clientId'];
        }

        $invEmplCheck = '-1';
        if (isset($_POST['invEmpl']) && $_POST['invEmpl'] === '1') {
            $invEmplCheck = '1';
        }

        $invCompCheck = '0';
        if (isset($_POST['invComp'])) {
            $invCompCheck = $_POST['invComp'] === 'on' || $_POST['invComp'] === '1' ? '1' : '0';
        }

        $paidCheck = '0';
        if (isset($_POST['paid'])) {
            $paidCheck = ($_POST['paid'] === 'on' || $_POST['paid'] === '1') ? '1' : '0';
        }

        // Use UTC here as all data stored in the database should be UTC (start in user's timezone and convert to UTC).
        // Default view: This Month (start and end of current month in user's timezone).
        $dateFrom = dtHelper()->userNow()->startOfMonth()->setToDbTimezone();
        $dateTo = dtHelper()->userNow()->endOfMonth()->setToDbTimezone();

        if (! empty($_POST['dateFrom'])) {
            $dateFrom = dtHelper()->parseUserDateTime($_POST['dateFrom'])->setToDbTimezone();
        }

        if (! empty($_POST['dateTo'])) {
            $dateTo = dtHelper()->parseUserDateTime($_POST['dateTo'])->setToDbTimezone();
        }

        $userId = session('userdata.id');
        $allProjects = $this->projectService->getProjectsUserHasAccessTo($userId);
        if (! is_array($allProjects)) {
            $allProjects = [];
        }

        $allClients = $this->clientService->getAll();

        $this->tpl->assign('dateFrom', $dateFrom);
        $this->tpl->assign('dateTo', $dateTo);
        $this->tpl->assign('actKind', $kind);
        $this->tpl->assign('projectFilter', $projectId);
        $this->tpl->assign('allProjects', $allProjects);
        $this->tpl->assign('clientFilter', $clientId);
        $this->tpl->assign('allClients', $allClients);
        $this->tpl->assign('invEmpl', $invEmplCheck);
        $this->tpl->assign('invComp', $invCompCheck);
        $this->tpl->assign('paid', $paidCheck);
        $this->tpl->assign('kind', $this->timesheetService->getLoggableHourTypes());
        $this->tpl->assign('allTimesheets', $this->timesheetService->getAll(
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            projectId: $projectId,
            kind: $kind,
            userId: $userId,
            invEmpl: $invEmplCheck,
            invComp: $invCompCheck,
            paid: $paidCheck,
            ticketFilter: '-1',
            clientId: (string) $clientId
        ));

        // Pass user's hours format preference to template for CSV export
        $userId = session('userdata.id');
        $hoursFormat = 'decimal';
        if ($userId) {
            $settingsService = app()->make(\Leantime\Domain\Setting\Services\Setting::class);
            $hoursFormat = $settingsService->getSetting('usersettings.'.$userId.'.hours_format', 'decimal');
        }
        $this->tpl->assign('hoursFormat', $hoursFormat);

        return $this->tpl->display('timesheets.showMyList');
    }
}
