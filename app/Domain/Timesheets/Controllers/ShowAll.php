<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Carbon\CarbonInterface;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Timesheets\Permissions\TimesheetsPermissions;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

class ShowAll extends Controller
{
    private ProjectService $projectService;

    private ClientService $clientService;

    private TimesheetService $timesheetService;

    private TicketService $ticketService;

    private UserService $userService;

    /**
     * Initializes dependencies.
     */
    public function init(
        ProjectService $projectService,
        TimesheetService $timesheetService,
        ClientService $clientService,
        TicketService $ticketService,
        UserService $userService
    ): void {
        $this->timesheetService = $timesheetService;
        $this->projectService = $projectService;
        $this->clientService = $clientService;
        $this->ticketService = $ticketService;
        $this->userService = $userService;
    }

    /**
     * Displays the list of all timesheets.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(TimesheetsPermissions::MANAGE, global: true)]
    public function get(array $params): Response
    {
        session(['lastPage' => BASE_URL.'/timesheets/showAll']);

        $this->assignTemplateVars(
            dateFrom: dtHelper()->userNow()->startOfWeek(CarbonInterface::MONDAY)->setToDbTimezone(),
            dateTo: dtHelper()->userNow()->endOfMonth()->setToDbTimezone(),
            kind: 'all',
            userId: null,
            invEmplCheck: '-1',
            invCompCheck: '0',
            paidCheck: '0',
            projectFilter: -1,
            ticketFilter: -1,
            clientId: -1,
        );

        return $this->tpl->display('timesheets.showAll');
    }

    /**
     * Handles timesheet filter changes and invoice saves.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(TimesheetsPermissions::MANAGE, global: true)]
    public function post(array $params): Response
    {
        session(['lastPage' => BASE_URL.'/timesheets/showAll']);

        if (isset($_POST['saveInvoice'])) {
            $this->timesheetService->updateInvoices(
                $_POST['invoicedEmpl'] ?? [],
                $_POST['invoicedComp'] ?? [],
                $_POST['paid'] ?? []
            );
        }

        $filters = $this->timesheetService->buildShowAllFilters($_POST);

        $this->assignTemplateVars(
            dateFrom: $filters['dateFrom'],
            dateTo: $filters['dateTo'],
            kind: $filters['kind'],
            userId: $filters['userId'],
            invEmplCheck: $filters['invEmplCheck'],
            invCompCheck: $filters['invCompCheck'],
            paidCheck: $filters['paidCheck'],
            projectFilter: $filters['projectFilter'],
            ticketFilter: $filters['ticketFilter'],
            clientId: $filters['clientId'],
        );

        return $this->tpl->display('timesheets.showAll');
    }

    /**
     * Assigns common template variables for the timesheet list view.
     */
    private function assignTemplateVars(
        CarbonInterface $dateFrom,
        CarbonInterface $dateTo,
        string $kind,
        ?int $userId,
        string $invEmplCheck,
        string $invCompCheck,
        string $paidCheck,
        int|string $projectFilter,
        int|string $ticketFilter,
        int|string $clientId,
    ): void {
        $selectedTicketProjectId = null;
        if ($ticketFilter != '' && $ticketFilter != -1) {
            $selectedTicket = $this->ticketService->getTicket($ticketFilter);
            if ($selectedTicket) {
                $selectedTicketProjectId = $selectedTicket->projectId;
            }
        }

        $resolvedTicketFilter = $this->timesheetService->resolveShowAllTicketFilter(
            $projectFilter,
            $ticketFilter,
            $selectedTicketProjectId
        );

        $this->tpl->assign('employeeFilter', $userId);
        $this->tpl->assign('employees', $this->userService->getAll());
        $this->tpl->assign('dateFrom', $dateFrom);
        $this->tpl->assign('dateTo', $dateTo);
        $this->tpl->assign('actKind', $kind);
        $this->tpl->assign('kind', $this->timesheetService->getBookedHourTypes());
        $this->tpl->assign('invComp', $invCompCheck);
        $this->tpl->assign('invEmpl', $invEmplCheck);
        $this->tpl->assign('paid', $paidCheck);
        $this->tpl->assign('allProjects', $this->projectService->getAll());
        $this->tpl->assign('projectFilter', $projectFilter);
        $this->tpl->assign('allTickets', ($projectFilter == -1) ? [] : $this->ticketService->getAll(['currentProject' => $projectFilter]));
        $this->tpl->assign('ticketFilter', $ticketFilter);
        $this->tpl->assign('clientFilter', $clientId);
        $this->tpl->assign('allClients', $this->clientService->getAll());
        $this->tpl->assign('allTimesheets', $this->timesheetService->getAll(
            $dateFrom,
            $dateTo,
            (int) $projectFilter,
            $kind,
            $userId,
            $invEmplCheck,
            $invCompCheck,
            $resolvedTicketFilter,
            $paidCheck,
            $clientId
        ));
    }
}
