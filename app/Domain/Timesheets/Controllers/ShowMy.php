<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Leantime\Domain\Auth\Services\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class ShowMy extends Controller
{
    private TimesheetRepository $timesheetsRepo;
    private ProjectRepository $projects;
    private TicketRepository $tickets;
    private UserRepository $userRepo;

    /**
     * init - initialze private variables
     *
     * @param TimesheetRepository $timesheetsRepo
     * @param ProjectRepository   $projects
     * @param TicketRepository    $tickets
     * @param UserRepository      $userRepo
     *
     * @return void
     */
    public function init(
        TimesheetRepository $timesheetsRepo,
        ProjectRepository $projects,
        TicketRepository $tickets,
        UserRepository $userRepo
    ): void {
        $this->timesheetsRepo = $timesheetsRepo;
        $this->projects = $projects;
        $this->tickets = $tickets;
        $this->userRepo = $userRepo;
    }

    /**
     * run - display template and edit data
     *
     * @return Response
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function run(): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

        // Use UTC here as all data stored in the database should be UTC.
        $fromData = Carbon::now('UTC')->startOfWeek();

        $kind = 'all';
        if (isset($_POST['search'])) {
            // User date comes is in user date format and user timezone. Change it to utc.
            if (!empty($_POST['startDate'])) {
                $fromData = new Carbon($_POST['startDate'], 'UTC');
            }
        }

        if (isset($_POST['saveTimeSheet'])) {
            $this->saveTimeSheet($_POST);
            $this->tpl->setNotification('Timesheet successfully updated', 'success');
        }

        $myTimesheets = $this->timesheetsRepo->getWeeklyTimesheets(-1, $fromData, $_SESSION['userdata']['id']);

        $this->tpl->assign('dateFrom', $fromData);
        $this->tpl->assign('actKind', $kind);
        $this->tpl->assign('kind', $this->timesheetsRepo->kind);
        $this->tpl->assign('allProjects', $this->projects->getUserProjects(userId: $_SESSION["userdata"]["id"], projectTypes: "project"));
        $this->tpl->assign('allTickets', $this->tickets->getUsersTickets($_SESSION["userdata"]["id"], -1));
        $this->tpl->assign('allTimesheets', $myTimesheets);

        return $this->tpl->display('timesheets.showMy');
    }

    /**
     * @param array $postData
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function saveTimeSheet(array $postData): void
    {
        $ticketId = "";
        $currentTimesheetId = -1;
        $userinfo = $this->userRepo->getUser($_SESSION["userdata"]["id"]);

        foreach ($postData as $key => $dateEntry) {
            // Receiving a string of
            // TICKET ID | New or existing timesheetID | Current Date | Type of booked hours
            $tempData = explode("|", $key);

            if (count($tempData) == 4) {
                $ticketId = $tempData[0];
                $isCurrentTimesheetEntry = $tempData[1];
                $currentDate = $tempData[2];
                $hours = $dateEntry;

                // No ticket ID set, ticket id comes from form fields
                if ($ticketId == "new") {
                    $ticketId = $postData["ticketId"];
                    $kind = $postData["kindId"];
                } else {
                    $kind = $tempData[3];
                }

                $values = array(
                    "userId" => $_SESSION["userdata"]["id"],
                    "ticket" => $ticketId,
                    "date" => format($currentDate)->isoDate(),
                    "hours" => $hours,
                    "kind" => $kind,
                    "rate" => $userinfo["wage"],
                );

                if ($isCurrentTimesheetEntry == "new") {
                    if ($values["hours"] > 0) {
                        $this->timesheetsRepo->simpleInsert($values);
                    }
                } else {
                    $this->timesheetsRepo->updateHours($values);
                }
            }
        }
    }
}
