<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Timesheets\Permissions\TimesheetsPermissions;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Symfony\Component\HttpFoundation\Response;

class EditTime extends Controller
{
    private TimesheetService $timesheetService;

    private ProjectService $projectService;

    private TicketService $ticketService;

    private ClientService $clientService;

    /**
     * Initializes dependencies.
     */
    public function init(
        TimesheetService $timesheetService,
        ProjectService $projectService,
        TicketService $ticketService,
        ClientService $clientService
    ): void {
        $this->timesheetService = $timesheetService;
        $this->projectService = $projectService;
        $this->ticketService = $ticketService;
        $this->clientService = $clientService;
    }

    /**
     * Displays the edit time form.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(TimesheetsPermissions::EDIT, global: true)]
    public function get(array $params): Response
    {
        if (! isset($params['id'])) {
            return $this->tpl->displayPartial('errors.error403');
        }

        $id = (int) $params['id'];
        // getTimesheetForEdit routes through the gated getTimesheet (own → view, another user's →
        // manage), returning null for an entry the user may not access — this fences ownership,
        // replacing the former manual role/owner check.
        $values = $this->timesheetService->getTimesheetForEdit($id);

        if ($values === null) {
            return $this->tpl->displayPartial('errors.error403');
        }

        $this->tpl->assign('values', $values);
        $this->tpl->assign('info', '');
        $this->assignTemplateVars();

        return $this->tpl->displayPartial('timesheets.editTime');
    }

    /**
     * Handles time entry update.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(TimesheetsPermissions::EDIT, global: true)]
    public function post(array $params): Response
    {
        if (! isset($params['id'])) {
            return $this->tpl->displayPartial('errors.error403');
        }

        $id = (int) $params['id'];
        // Fences ownership via the gated read; the save itself goes through the gated
        // validateAndUpdateTime → updateTime (own → edit, another user's → manage).
        $values = $this->timesheetService->getTimesheetForEdit($id);

        if ($values === null) {
            return $this->tpl->displayPartial('errors.error403');
        }

        if (isset($_POST['saveForm'])) {
            $values = $this->timesheetService->applyEditTimePostUpdates($_POST, $values);
            $values = $this->timesheetService->processEditTimeInvoiceFields($_POST, $values);

            $result = $this->timesheetService->validateAndUpdateTime($id, $values);
            $values = $result['values'];

            if ($result['notification'] !== null) {
                $this->tpl->setNotification($result['notification']['message'], $result['notification']['type']);
            }
        }

        $this->tpl->assign('values', $values);
        $this->tpl->assign('info', '');
        $this->assignTemplateVars();

        return $this->tpl->displayPartial('timesheets.editTime');
    }

    /**
     * Assigns common template variables for the edit form.
     */
    private function assignTemplateVars(): void
    {
        $this->tpl->assign('allClients', $this->clientService->getAll());
        $this->tpl->assign('allProjects', $this->projectService->getAll(showClosedProjects: false));
        $this->tpl->assign('allTickets', $this->ticketService->getAll());
        $this->tpl->assign('kind', $this->timesheetService->getLoggableHourTypes());
    }
}
