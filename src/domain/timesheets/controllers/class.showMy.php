<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\base\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use Datetime;
    use leantime\domain\services\auth;

    class showMy extends controller
    {

        private $timesheetsRepo;
        private $projects;
        private $tickets;
        private $userRepo;

        /**
         * init - initialze private variables
         *
         * @access public
         */
        public function init()
        {

            $this->timesheetsRepo = new repositories\timesheets();
            $this->projects = new repositories\projects();
            $this->tickets = new repositories\tickets();
            $this->userRepo = new repositories\users();

        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {
            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor], true);

            $invEmplCheck = '0';
            $invCompCheck = '0';

            $dateFrom = date('Y-m-d', time() + (1 - date('w')) * 24 * 3600);

            $kind = 'all';

            if (isset($_POST['search']) === true) {

                if (isset($_POST['startDate']) === true && $_POST['startDate'] != "") {
                    try {

                        $dateFrom = $this->language->getISODateString($_POST['startDate']);

                    } catch (Exception $e) {
                        $dateFrom = date('Y-m-d', time() + (1 - date('w')) * 24 * 3600);
                    }
                }
            }

            if (isset($_POST['saveTimeSheet']) === true) {

                if (isset($_POST['startDate']) === true && $_POST['startDate'] != "") {
                    try {

                        $dateFrom = $this->language->getISODateString($_POST['startDate']);

                    } catch (Exception $e) {
                        $dateFrom = date('Y-m-d', time() + (1 - date('w')) * 24 * 3600);
                    }
                }

                $this->saveTimeSheet($_POST);

                $this->tpl->setNotification('Timesheet successfully updated', 'success');
            }

            $myTimesheets = $this->timesheetsRepo->getWeeklyTimesheets(-1, $dateFrom, $_SESSION['userdata']['id']);

            $this->tpl->assign('dateFrom', new DateTime($dateFrom));
            $this->tpl->assign('actKind', $kind);
            $this->tpl->assign('kind', $this->timesheetsRepo->kind);
            $this->tpl->assign('allProjects', $this->projects->getUserProjects($_SESSION["userdata"]["id"]));
            $this->tpl->assign('allTickets', $this->tickets->getUsersTickets($_SESSION["userdata"]["id"], -1));
            $this->tpl->assign('allTimesheets', $myTimesheets);
            $this->tpl->display('timesheets.showMy');

        }

        public function saveTimeSheet($postData)
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
                    }else{
                        $kind = $tempData[3];
                    }

                    $values = array(
                        "userId" => $_SESSION["userdata"]["id"],
                        "ticket" => $ticketId,
                        "date" => $currentDate,
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
