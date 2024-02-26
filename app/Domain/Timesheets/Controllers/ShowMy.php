<?php

namespace Leantime\Domain\Timesheets\Controllers {

    use DateTime;
    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;
    use Leantime\Domain\Auth\Services\Auth;

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
         * @access public
         */
        public function init(
            TimesheetRepository $timesheetsRepo,
            ProjectRepository $projects,
            TicketRepository $tickets,
            UserRepository $userRepo
        ) {
            $this->timesheetsRepo = $timesheetsRepo;
            $this->projects = $projects;
            $this->tickets = $tickets;
            $this->userRepo = $userRepo;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

            $invEmplCheck = '0';
            $invCompCheck = '0';

            //Start with user monday 00:00 of this week in user timezone.
            $dateStart = new \DateTime('Monday this week', new \DateTimeZone($_SESSION['usersettings.timezone']));

            //Change to UTC for processing
            $dateStart->setTimezone(new \DateTimeZone("UTC"));
            $dateFrom = $dateStart->format("Y-m-d H:i:s");

            $kind = 'all';

            if (isset($_POST['search']) === true) {

                //User date comes is in user date format and user timezone. change to utc and iso
                if (isset($_POST['startDate']) === true && $_POST['startDate'] != "") {
                    $dateFrom = format($_POST['startDate'])->isoDate();
                }
            }

            if (isset($_POST['saveTimeSheet']) === true) {
                $this->saveTimeSheet($_POST);

                $this->tpl->setNotification('Timesheet successfully updated', 'success');
            }

            $myTimesheets = $this->timesheetsRepo->getWeeklyTimesheets(-1, $dateFrom, $_SESSION['userdata']['id']);


            //Ensure we assume this is UTC
            $dateFromDate = new  DateTime($dateFrom, new \DateTimeZone("UTC"));
            $this->tpl->assign('dateFrom',$dateFromDate);
            $this->tpl->assign('actKind', $kind);
            $this->tpl->assign('kind', $this->timesheetsRepo->kind);
            $this->tpl->assign('allProjects', $this->projects->getUserProjects(userId: $_SESSION["userdata"]["id"], projectTypes: "project" ));
            $this->tpl->assign('allTickets', $this->tickets->getUsersTickets($_SESSION["userdata"]["id"], -1));
            $this->tpl->assign('allTimesheets', $myTimesheets);

            return $this->tpl->display('timesheets.showMy');
        }

        /**
         * @param $postData
         * @return void
         */
        public function saveTimeSheet($postData): void
        {
            $ticketId = "";

            $currentTimesheetId = -1;
            $userinfo = $this->userRepo->getUser($_SESSION["userdata"]["id"]);

            foreach ($postData as $key => $dateEntry) {
                //Receiving a string of
                //TICKET ID | New or existing timesheetID | Current Date | Type of booked hours
                $tempData = explode("|", $key);

                if (count($tempData) == 4) {
                    $ticketId = $tempData[0];
                    $isCurrentTimesheetEntry = $tempData[1];
                    $currentDate = $tempData[2];
                    $hours = $dateEntry;

                    //No ticket ID set, ticket id comes from form fields
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
                        $this->timesheetsRepo->UpdateHours($values);
                    }
                }
            }
        }
    }

}
