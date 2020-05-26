<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use Datetime;
    class showMy
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $timesheetsRepo = new repositories\timesheets();

            $invEmplCheck = '0';
            $invCompCheck = '0';

            $projects = new repositories\projects();
            $helper = new core\helper();
            $tickets = new repositories\tickets();
            $language = new core\language();


            $dateFrom = date('Y-m-d', time() + (1 - date('w')) * 24 * 3600);

            $kind = 'all';

            if (isset($_POST['search']) === true) {

                if (isset($_POST['startDate']) === true && $_POST['startDate'] != "") {
                    try {

                        $dateFrom = $language->getISODateString($_POST['startDate']);

                    } catch (Exception $e) {
                        $dateFrom = date('Y-m-d', time() + (1 - date('w')) * 24 * 3600);
                    }
                }
            }

            if (isset($_POST['saveTimeSheet']) === true) {

                if (isset($_POST['startDate']) === true && $_POST['startDate'] != "") {
                    try {

                        $dateFrom = $language->getISODateString($_POST['startDate']);

                    } catch (Exception $e) {
                        $dateFrom = date('Y-m-d', time() + (1 - date('w')) * 24 * 3600);
                    }
                }

                $this->saveTimeSheet($_POST);

                $tpl->setNotification('Timesheet successfully updated', 'success');
            }

            $myTimesheets = $timesheetsRepo->getWeeklyTimesheets(-1, $dateFrom, $_SESSION['userdata']['id']);

            $tpl->assign('dateFrom', new DateTime($dateFrom));
            $tpl->assign('actKind', $kind);
            $tpl->assign('kind', $timesheetsRepo->kind);
            $tpl->assign('helper', $helper);
            $tpl->assign('allProjects', $projects->getUserProjects($_SESSION["userdata"]["id"]));
            $tpl->assign('allTickets', $tickets->getUsersTickets($_SESSION["userdata"]["id"], -1));
            $tpl->assign('allTimesheets', $myTimesheets);
            $tpl->display('timesheets.showMy');

        }

        public function saveTimeSheet($postData)
        {
            $ticketId = "";

            $currentTimesheetId = -1;
            $user = new repositories\users();
            $userinfo = $user->getUser($_SESSION["userdata"]["id"]);
            $timesheetRepo = new repositories\timesheets();

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

                            $timesheetRepo->simpleInsert($values);
                        }

                    } else {

                        $timesheetRepo->UpdateHours($values);

                    }
                }
            }
        }


    }

}
