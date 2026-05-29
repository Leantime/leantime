<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Carbon\Carbon;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Symfony\Component\HttpFoundation\Response;

class EditTime extends Controller
{
    private TimesheetService $timesheetService;

    private ProjectService $projectService;

    private TicketService $ticketService;

    private ClientService $clientService;

    /**
     * The placeholder date returned from the database when no date has been set.
     */
    private const EMPTY_DATE = '0000-00-00 00:00:00';

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
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

        if (! Auth::userIsAtLeast(Roles::$editor) || ! isset($params['id'])) {
            return $this->tpl->displayPartial('errors.error403');
        }

        $id = (int) $params['id'];
        $values = $this->buildValuesFromTimesheet($id);

        if ($values === null) {
            return $this->tpl->displayPartial('errors.error403');
        }

        if (! Auth::userIsAtLeast(Roles::$manager) && session('userdata.id') != $values['userId']) {
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
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

        if (! Auth::userIsAtLeast(Roles::$editor) || ! isset($params['id'])) {
            return $this->tpl->displayPartial('errors.error403');
        }

        $id = (int) $params['id'];
        $values = $this->buildValuesFromTimesheet($id);

        if ($values === null) {
            return $this->tpl->displayPartial('errors.error403');
        }

        if (! Auth::userIsAtLeast(Roles::$manager) && session('userdata.id') != $values['userId']) {
            return $this->tpl->displayPartial('errors.error403');
        }

        if (isset($_POST['saveForm'])) {
            $values = $this->applyPostUpdates($values);
            $values = $this->processInvoiceFields($values);
            $this->validateAndUpdate($id, $values);
        }

        $this->tpl->assign('values', $values);
        $this->tpl->assign('info', '');
        $this->assignTemplateVars();

        return $this->tpl->displayPartial('timesheets.editTime');
    }

    /**
     * Builds a values array from a timesheet entry, normalizing empty dates.
     *
     * @return array|null Null if the timesheet is not found
     */
    private function buildValuesFromTimesheet(int $id): ?array
    {
        $timesheet = $this->timesheetService->getTimesheet($id);

        if (! $timesheet) {
            return null;
        }

        $timesheet['invoicedEmplDate'] = $timesheet['invoicedEmplDate'] == self::EMPTY_DATE ? 'now' : $timesheet['invoicedEmplDate'];
        $timesheet['invoicedCompDate'] = $timesheet['invoicedCompDate'] == self::EMPTY_DATE ? 'now' : $timesheet['invoicedCompDate'];
        $timesheet['paidDate'] = $timesheet['paidDate'] == self::EMPTY_DATE ? 'now' : $timesheet['paidDate'];

        return [
            'id' => $id,
            'userId' => $timesheet['userId'],
            'ticket' => $timesheet['ticketId'],
            'project' => $timesheet['projectId'],
            'date' => new Carbon($timesheet['workDate'], 'UTC'),
            'kind' => $timesheet['kind'],
            'hours' => $timesheet['hours'],
            'description' => $timesheet['description'],
            'invoicedEmpl' => $timesheet['invoicedEmpl'],
            'invoicedComp' => $timesheet['invoicedComp'],
            'invoicedEmplDate' => new Carbon($timesheet['invoicedEmplDate'], 'UTC'),
            'invoicedCompDate' => new Carbon($timesheet['invoicedCompDate'], 'UTC'),
            'paid' => $timesheet['paid'],
            'paidDate' => new Carbon($timesheet['paidDate'], 'UTC'),
        ];
    }

    /**
     * Applies basic POST field updates to values.
     */
    private function applyPostUpdates(array $values): array
    {
        if (! empty($_POST['tickets'])) {
            $values['project'] = (int) $_POST['projects'];
            $values['ticket'] = (int) $_POST['tickets'];
        }

        if (! empty($_POST['kind'])) {
            $values['kind'] = $_POST['kind'];
        }

        if (! empty($_POST['date'])) {
            $values['date'] = dtHelper()->parseUserDateTime($_POST['date'], 'start')->formatDateTimeForDb();
        }

        if (! empty($_POST['hours'])) {
            $values['hours'] = (float) $_POST['hours'];
        }

        if (! empty($_POST['description'])) {
            $values['description'] = $_POST['description'];
        }

        return $values;
    }

    /**
     * Processes invoice and payment fields (manager-only).
     */
    private function processInvoiceFields(array $values): array
    {
        if (! Auth::userIsAtLeast(Roles::$manager)) {
            return $values;
        }

        if (! empty($_POST['invoicedEmpl'])) {
            if ($_POST['invoicedEmpl'] == 'on') {
                $values['invoicedEmpl'] = 1;
            }
            $values['invoicedEmplDate'] = ! empty($_POST['invoicedEmplDate'])
                ? dtHelper()->parseUserDateTime($_POST['invoicedEmplDate'], 'start')->formatDateTimeForDb()
                : dtHelper()->userNow()->formatDateTimeForDb();
        } else {
            $values['invoicedEmpl'] = 0;
            $values['invoicedEmplDate'] = '';
        }

        if (! empty($_POST['invoicedComp'])) {
            if ($_POST['invoicedComp'] == 'on') {
                $values['invoicedComp'] = 1;
            }
            $values['invoicedCompDate'] = ! empty($_POST['invoicedCompDate'])
                ? dtHelper()->parseUserDateTime($_POST['invoicedCompDate'], 'start')->formatDateTimeForDb()
                : dtHelper()->userNow()->formatDateTimeForDb();
        } else {
            $values['invoicedComp'] = 0;
            $values['invoicedCompDate'] = '';
        }

        if (! empty($_POST['paid'])) {
            if ($_POST['paid'] == 'on') {
                $values['paid'] = 1;
            }
            if (! empty($_POST['paidDate'])) {
                $date = dtHelper()->parseUserDateTime($_POST['paidDate'], 'start');
                $date->setTimezone('UTC');
                $values['paidDate'] = $date->formatDateTimeForDb();
            } else {
                $values['paidDate'] = dtHelper()->userNow()->formatDateTimeForDb();
            }
        } else {
            $values['paid'] = 0;
            $values['paidDate'] = '';
        }

        return $values;
    }

    /**
     * Validates the values and updates the time entry.
     * On success, reloads values from the database.
     */
    private function validateAndUpdate(int $id, array &$values): void
    {
        if ($values['ticket'] == '' || $values['project'] == '') {
            $this->tpl->setNotification('notifications.time_logged_error_no_ticket', 'error');

            return;
        }

        if ($values['kind'] == '') {
            $this->tpl->setNotification('notifications.time_logged_error_no_kind', 'error');

            return;
        }

        if ($values['date'] == '') {
            $this->tpl->setNotification('notifications.time_logged_error_no_date', 'error');

            return;
        }

        if ($values['hours'] == '' || $values['hours'] <= 0) {
            $this->tpl->setNotification('notifications.time_logged_error_no_hours', 'error');

            return;
        }

        try {
            $this->timesheetService->updateTime($values);
            $this->tpl->setNotification('notifications.time_logged_success', 'success');
        } catch (\Exception $e) {
            $this->tpl->setNotification('notifications.could_not_store_time', 'error');
        }

        $refreshed = $this->buildValuesFromTimesheet($id);
        if ($refreshed !== null) {
            $values = $refreshed;
        }
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
