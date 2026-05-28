<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Carbon\Carbon;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
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
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

        if (! Auth::userIsAtLeast(Roles::$editor)) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $this->tpl->assign('values', $this->getDefaultValues());
        $this->tpl->assign('info', '');
        $this->assignTemplateVars();

        return $this->tpl->display('timesheets.addTime');
    }

    /**
     * Handles time entry creation.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

        if (! Auth::userIsAtLeast(Roles::$editor)) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $info = '';
        $values = $this->getDefaultValues();

        if (isset($_POST['save']) || isset($_POST['saveNew'])) {
            $values = $this->parsePostValues($values);
            $info = $this->validateAndSave($values);

            if (isset($_POST['saveNew'])) {
                $values = $this->getDefaultValues();
            }
        }

        $this->tpl->assign('values', $values);
        $this->tpl->assign('info', $info);
        $this->assignTemplateVars();

        return $this->tpl->display('timesheets.addTime');
    }

    /**
     * Returns default empty values for the time entry form.
     */
    private function getDefaultValues(): array
    {
        return [
            'userId' => session('userdata.id'),
            'ticket' => '',
            'project' => '',
            'date' => '',
            'kind' => '',
            'hours' => '',
            'description' => '',
            'invoicedEmpl' => '',
            'invoicedComp' => '',
            'invoicedEmplDate' => '',
            'invoicedCompDate' => '',
            'paid' => '',
            'paidDate' => '',
        ];
    }

    /**
     * Parses POST data into a values array.
     */
    private function parsePostValues(array $values): array
    {
        if (isset($_POST['tickets']) && $_POST['tickets'] != '') {
            $tempArr = explode('|', $_POST['tickets']);
            $values['project'] = $tempArr[0];
            $values['ticket'] = $tempArr[1];
        }

        if (! empty($_POST['kind'])) {
            $values['kind'] = $_POST['kind'];
        }

        if (! empty($_POST['date'])) {
            $values['date'] = (new Carbon($_POST['date'], session('usersettings.timezone')))->setTimezone('UTC');
        }

        if (! empty($_POST['hours'])) {
            $values['hours'] = $_POST['hours'];
        }

        if (! empty($_POST['invoicedEmpl']) && $_POST['invoicedEmpl'] == 'on') {
            $values['invoicedEmpl'] = 1;
            if (! empty($_POST['invoicedEmplDate'])) {
                $values['invoicedEmplDate'] = Carbon::now(session('usersettings.timezone'))->setTimezone('UTC');
            }
        }

        if (! empty($_POST['invoicedComp']) && Auth::userIsAtLeast(Roles::$manager)) {
            if ($_POST['invoicedComp'] == 'on') {
                $values['invoicedComp'] = 1;
            }
            if (! empty($_POST['invoicedCompDate'])) {
                $values['invoicedCompDate'] = Carbon::now(session('usersettings.timezone'))->setTimezone('UTC');
            }
        }

        if (! empty($_POST['paid']) && Auth::userIsAtLeast(Roles::$manager)) {
            if ($_POST['paid'] == 'on') {
                $values['paid'] = 1;
            }
            if (! empty($_POST['paidDate'])) {
                $values['paidDate'] = Carbon::now(session('usersettings.timezone'))->setTimezone('UTC');
            }
        }

        if (! empty($_POST['description'])) {
            $values['description'] = $_POST['description'];
        }

        return $values;
    }

    /**
     * Validates the values and saves the time entry. Returns an info status string.
     */
    private function validateAndSave(array $values): string
    {
        if ($values['ticket'] == '' || $values['project'] == '') {
            return 'NO_TICKET';
        }

        if ($values['kind'] == '') {
            return 'NO_KIND';
        }

        if ($values['date'] == '') {
            return 'NO_DATE';
        }

        if ($values['hours'] == '' || $values['hours'] <= 0) {
            return 'NO_HOURS';
        }

        $this->timesheetService->addTime($values);

        return 'TIME_SAVED';
    }

    /**
     * Assigns common template variables.
     */
    private function assignTemplateVars(): void
    {
        $this->tpl->assign('allClients', $this->clientService->getAll());
        $this->tpl->assign('allProjects', $this->projectService->getAll(showClosedProjects: false));
        $this->tpl->assign('allTickets', $this->timesheetService->getAll(
            dateFrom: dtHelper()->userNow()->subYears(10)->setToDbTimezone(),
            dateTo: dtHelper()->userNow()->addYears(10)->setToDbTimezone(),
        ));
        $this->tpl->assign('kind', $this->timesheetService->getLoggableHourTypes());
    }
}
