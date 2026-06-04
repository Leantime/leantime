<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Timesheets\Permissions\TimesheetsPermissions;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Symfony\Component\HttpFoundation\Response;

class AddTime extends Controller
{
    private TimesheetService $timesheetService;

    private ProjectService $projectService;

    private ClientService $clientService;

    /**
     * Initializes dependencies.
     */
    public function init(
        TimesheetService $timesheetService,
        ProjectService $projectService,
        ClientService $clientService
    ): void {
        $this->timesheetService = $timesheetService;
        $this->projectService = $projectService;
        $this->clientService = $clientService;
    }

    /**
     * Displays the add time form.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(TimesheetsPermissions::VIEW, global: true)]
    public function get(array $params): Response
    {
        $this->tpl->assign('values', $this->timesheetService->getDefaultTimeValues());
        $this->tpl->assign('info', '');
        $this->assignTemplateVars();

        return $this->tpl->display('timesheets.addTime');
    }

    /**
     * Handles time entry creation.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(TimesheetsPermissions::CREATE, global: true)]
    public function post(array $params): Response
    {
        $info = '';
        $values = $this->timesheetService->getDefaultTimeValues();

        if (isset($_POST['save']) || isset($_POST['saveNew'])) {
            $values = $this->timesheetService->parseAddTimePostValues($_POST, $values);
            $info = $this->timesheetService->validateAndSaveTime($values);

            if (isset($_POST['saveNew'])) {
                $values = $this->timesheetService->getDefaultTimeValues();
            }
        }

        $this->tpl->assign('values', $values);
        $this->tpl->assign('info', $info);
        $this->assignTemplateVars();

        return $this->tpl->display('timesheets.addTime');
    }

    /**
     * Assigns common template variables.
     */
    private function assignTemplateVars(): void
    {
        $this->tpl->assign('allClients', $this->clientService->getAll());
        $this->tpl->assign('allProjects', $this->projectService->getAll(showClosedProjects: false));
        // Scope the picker to the current user's own timesheets (editor → timesheets.view);
        // an unscoped call would require timesheets.manage and over-fetch every user's entries.
        $this->tpl->assign('allTickets', $this->timesheetService->getAll(
            dateFrom: dtHelper()->userNow()->subYears(10)->setToDbTimezone(),
            dateTo: dtHelper()->userNow()->addYears(10)->setToDbTimezone(),
            userId: (int) session('userdata.id'),
        ));
        $this->tpl->assign('kind', $this->timesheetService->getLoggableHourTypes());
    }
}
