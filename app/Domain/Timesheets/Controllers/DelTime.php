<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Illuminate\Http\RedirectResponse;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
use Symfony\Component\HttpFoundation\Response;

class DelTime extends Controller
{
    private TimesheetRepository $timesheetsRepo;

    /**
     * init - initialize private variable
     */
    public function init(TimesheetRepository $timesheetsRepo): void
    {
        $this->timesheetsRepo = $timesheetsRepo;
    }

    /**
     * run - display template and edit data
     */
    public function run(): Response|RedirectResponse
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$teamlead, Roles::$editor], true);

        if (isset($_GET['id']) === true) {
            $id = (int) ($_GET['id']);

            // Ownership check: a user may only delete their own time entries.
            // TL+ / managers / admins may delete any entry.
            $timesheet = $this->timesheetsRepo->getTimesheet($id);

            if ($timesheet === false) {
                return $this->tpl->displayPartial('errors.error404');
            }

            $isOwner = (int) ($timesheet['userId'] ?? 0) === (int) session('userdata.id');

            if (! $isOwner && ! Auth::userIsAtLeast(Roles::$teamlead, true)) {
                return $this->tpl->displayPartial('errors.error403');
            }

            if (isset($_POST['del']) === true) {
                $this->timesheetsRepo->deleteTime($id);

                $this->tpl->setNotification('notifications.time_deleted_successfully', 'success');

                if (session()->exists('lastPage')) {
                    return Frontcontroller::redirect(session('lastPage'));
                } else {
                    return Frontcontroller::redirect(BASE_URL.'/timsheets/showMyList');
                }
            }

            $this->tpl->assign('id', $id);

            return $this->tpl->displayPartial('timesheets.delTime');
        } else {
            return $this->tpl->displayPartial('errors.error403');
        }
    }
}
