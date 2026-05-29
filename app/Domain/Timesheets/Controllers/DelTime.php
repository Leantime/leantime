<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Symfony\Component\HttpFoundation\Response;

class DelTime extends Controller
{
    private TimesheetService $timesheetService;

    /**
     * Initializes dependencies.
     */
    public function init(TimesheetService $timesheetService): void
    {
        $this->timesheetService = $timesheetService;
    }

    /**
     * Displays the delete time confirmation dialog.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

        if (! isset($params['id'])) {
            return $this->tpl->displayPartial('errors.error403');
        }

        $this->tpl->assign('id', (int) $params['id']);

        return $this->tpl->displayPartial('timesheets.delTime');
    }

    /**
     * Handles time entry deletion.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

        if (! isset($params['id'])) {
            return $this->tpl->displayPartial('errors.error403');
        }

        $id = (int) $params['id'];

        if (isset($_POST['del'])) {
            $result = $this->timesheetService->deleteTime($id);

            if ($result === true) {
                $this->tpl->setNotification('notifications.time_deleted_successfully', 'success');

                if (session()->exists('lastPage')) {
                    return Frontcontroller::redirect(session('lastPage'));
                }

                return Frontcontroller::redirect(BASE_URL.'/timesheets/showMyList');
            }

            $this->tpl->setNotification('notifications.no_permission_delete', 'error');
        }

        $this->tpl->assign('id', $id);

        return $this->tpl->displayPartial('timesheets.delTime');
    }
}
