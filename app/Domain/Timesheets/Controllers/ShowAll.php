<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Carbon\CarbonInterface;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Symfony\Component\HttpFoundation\Response;

class ShowAll extends Controller
{
    private ProjectService $projectService;

    private ClientService $clientService;

    private TimesheetService $timesheetsService;

    private TicketService $ticketService;

    /**
     * init - initialize private variables
     */
    public function init(
        ProjectService $projectService,
        TimesheetService $timesheetsService,
        ClientService $clientService,
        TicketService $ticketService
    ): void {
        $this->timesheetsService = $timesheetsService;
        $this->projectService = $projectService;
        $this->clientService = $clientService;
        $this->ticketService = $ticketService;
    }

    /**
     * run - display template and edit data
     *
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function run(): Response
    {
        // Only admins and employees
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

        session(['lastPage' => BASE_URL.'/timesheets/showAll']);

        if (isset($_POST['saveInvoice']) === true) {
            $invEmpl = [];
            $invComp = [];
            $paid = [];

            if (isset($_POST['invoicedEmpl']) === true) {
                $invEmpl = $_POST['invoicedEmpl'];
            }

            if (isset($_POST['invoicedComp']) === true) {
                $invComp = $_POST['invoicedComp'];
            }

            if (isset($_POST['paid']) === true) {
                $paid = $_POST['paid'];
            }

            $this->timesheetsService->updateInvoices($invEmpl, $invComp, $paid);
        }

        $invCompCheck = '0';
        $kind = 'all';
        $userId = null;

        if (! empty($_POST['kind'])) {
            $kind = strip_tags($_POST['kind']);
        }

        if (! empty($_POST['userId'])) {
            $userId = intval(strip_tags($_POST['userId']));
        }

        $dateFrom = dtHelper()->userNow()->startOfWeek(CarbonInterface::MONDAY)->setToDbTimezone();
        if (! empty($_POST['dateFrom'])) {
            $dateFrom = dtHelper()->parseUserDateTime($_POST['dateFrom'])->setToDbTimezone();
        }

        $dateTo = dtHelper()->userNow()->endOfMonth()->setToDbTimezone();
        if (! empty($_POST['dateTo'])) {
            $dateTo = dtHelper()->parseUserDateTime($_POST['dateTo'])->setToDbTimezone();
        }

        $invEmplCheck = '-1';
        if (isset($_POST['invEmpl']) && $_POST['invEmpl'] === '1') {
            $invEmplCheck = '1';
        }

        if (isset($_POST['invComp'])) {
            $invCompCheck = ($_POST['invComp']);

            if ($invCompCheck == 'on') {
                $invCompCheck = '1';
            } else {
                $invCompCheck = '0';
            }
        }

        if (isset($_POST['paid'])) {
            $paidCheck = $_POST['paid'];

            if ($paidCheck == 'on') {
                $paidCheck = '1';
            } else {
                $paidCheck = '0';
            }
        } else {
            $paidCheck = '0';
        }

        $projectFilter = -1;
        if (! empty($_POST['project'])) {
            $selectedProjects = $_POST['project'];

            if (is_array($selectedProjects)) {
                $selectedProjects = array_map(static fn ($value) => (int) $value, $selectedProjects);

                // Remove any entries that are zero/empty to avoid invalid IDs
                $selectedProjects = array_values(array_filter($selectedProjects, static fn ($value) => $value !== 0));

                if (in_array(-1, $selectedProjects, true) || empty($selectedProjects)) {
                    $projectFilter = -1;
                } else {
                    $projectFilter = $selectedProjects;
                }
            } else {
                $projectFilter = (int) strip_tags($selectedProjects);
            }
        }

        $ticketFilter = -1;
        if (! empty($_POST['ticket'])) {
            $ticketFilter = (int) strip_tags($_POST['ticket']);
        }

        $clientId = -1;
        if (! empty($_POST['clientId'])) {
            $clientId = (int) strip_tags($_POST['clientId']);
        }

        // Determine if the selected ticket is in the selected project
        $projectMismatch = false;
        if ($ticketFilter != '' && $ticketFilter > 0) {
            $selectedTicket = $this->ticketService->getTicket($ticketFilter);

            if ($selectedTicket) {
                if (is_array($projectFilter)) {
                    if (! in_array((int) $selectedTicket->projectId, $projectFilter, true)) {
                        $projectMismatch = true;
                    }
                } elseif ($selectedTicket->projectId != $projectFilter) {
                    $projectMismatch = true;
                }
            }
        }

        $user = app()->make(UserRepository::class);
        $employees = $user->getAll();

        $this->tpl->assign('employeeFilter', $userId);
        $this->tpl->assign('employees', $employees);
        $this->tpl->assign('dateFrom', $dateFrom);
        $this->tpl->assign('dateTo', $dateTo);

        $this->tpl->assign('actKind', $kind);
        $this->tpl->assign('kind', $this->timesheetsService->getBookedHourTypes());
        $this->tpl->assign('invComp', $invCompCheck);
        $this->tpl->assign('invEmpl', $invEmplCheck);
        $this->tpl->assign('paid', $paidCheck);
        $this->tpl->assign('allProjects', $this->projectService->getAll());
        $this->tpl->assign('projectFilter', $projectFilter);

        $ticketDropdownProjects = $projectFilter;
        if (is_array($ticketDropdownProjects)) {
            // Ticket filter only supports single project selection; disable dropdown when multiple selected
            $ticketDropdownProjects = -1;
        }

        $this->tpl->assign('allTickets', ($ticketDropdownProjects == -1) ? [] : $this->ticketService->getAll(['currentProject' => $ticketDropdownProjects]));
        $this->tpl->assign('ticketFilter', $ticketFilter);
        $this->tpl->assign('clientFilter', $clientId);
        $this->tpl->assign('allClients', $this->clientService->getAll());
        $resolvedProjectFilter = is_array($projectFilter) ? $projectFilter : (int) $projectFilter;

        $ticketParameter = '-1';
        if (! $projectMismatch && ! is_array($projectFilter) && $projectFilter != -1) {
            $ticketParameter = $ticketFilter ?: '-1';
        }

        $this->tpl->assign('allTimesheets', $this->timesheetsService->getAll(
            $dateFrom,
            $dateTo,
            $resolvedProjectFilter,
            $kind,
            $userId,
            $invEmplCheck,
            $invCompCheck,
            $ticketParameter,
            $paidCheck,
            $clientId
        ));

        // Pass user's hours format preference to template for CSV export
        $userId = session('userdata.id');
        $hoursFormat = 'decimal';
        if ($userId) {
            $settingsService = app()->make(\Leantime\Domain\Setting\Services\Setting::class);
            $hoursFormat = $settingsService->getSetting('usersettings.'.$userId.'.hours_format', 'decimal');
        }
        $this->tpl->assign('hoursFormat', $hoursFormat);

        return $this->tpl->display('timesheets.showAll');
    }
}
