<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Leantime\Domain\Auth\Services\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class ShowMy extends Controller
{
    private timesheetService $timesheetService;
    private TimesheetRepository $timesheetRepo;
    private ProjectRepository $projects;
    private TicketRepository $tickets;
    private UserRepository $userRepo;

    /**
     * init - initialze private variables
     *
     * @param TimesheetService    $timesheetService
     * @param TimesheetRepository $timesheetRepo
     * @param ProjectRepository   $projects
     * @param TicketRepository    $tickets
     * @param UserRepository      $userRepo
     *
     * @return void
     */
    public function init(
        TimesheetService $timesheetService,
        TimesheetRepository $timesheetRepo,
        ProjectRepository $projects,
        TicketRepository $tickets,
        UserRepository $userRepo
    ): void {
        $this->timesheetService = $timesheetService;
        $this->timesheetRepo = $timesheetRepo;
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
                $fromData = Carbon::createFromFormat($_SESSION['usersettings.language.date_format'], $_POST['startDate'], 'UTC');
            }
        }

        if (isset($_POST['saveTimeSheet'])) {
            $this->saveTimeSheet($_POST);
            $this->tpl->setNotification('Timesheet successfully updated', 'success');
        }

        $myTimesheets = $this->timesheetService->getWeeklyTimesheets(-1, $fromData, $_SESSION['userdata']['id']);

        $this->tpl->assign('dateFrom', $fromData);
        $this->tpl->assign('actKind', $kind);
        $this->tpl->assign('kind', $this->timesheetRepo->kind);
        $this->tpl->assign('allProjects', $this->projects->getUserProjects(
            userId: $_SESSION["userdata"]["id"],
            projectTypes: "project"
        ));
        $this->tpl->assign('allTickets', $this->tickets->getUsersTickets(
            id: $_SESSION["userdata"]["id"],
            limit: -1
        ));
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
        $userinfo = $this->userRepo->getUser($_SESSION["userdata"]["id"]);

        foreach ($postData as $key => $dateEntry) {
            // The temp data should contain four parts, spectated by "|":
            // TICKET ID | new or existing | Current Date (user format) | Type of booked hours
            $tempData = explode("|", $key);
            if (count($tempData) === 4) {
                $ticketId = $tempData[0];
                $isNewEntry = 'new' === $tempData[1];
                $currentDate = new Carbon(str_replace('_', ' ', $tempData[2]));
                $hours = $dateEntry;
                $kind = $tempData[3];

                // No ticket ID set, ticket id comes from form fields
                if ($ticketId == "new") {
                    $ticketId = $postData["ticketId"];
                    $kind = $postData["kindId"];
                }

                $values = array(
                    "userId" => $_SESSION["userdata"]["id"],
                    "ticket" => $ticketId,
                    "date" => $currentDate,
                    "hours" => $hours,
                    "kind" => $kind,
                    "rate" => $userinfo["wage"],
                );

                if ($isNewEntry) {
                    if ($hours > 0) {
                        $this->timesheetRepo->simpleInsert($values);
                    }
                } else {
                    $this->timesheetRepo->updateHours($values);
                }
            }
        }
    }
}
