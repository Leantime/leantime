<?php

/**
 * showAll Class - show My Calender
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class showMyCalendar
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {
            include_once '../config/configuration.php';
            $config = new core\config();

            $tpl = new core\template();
            $calendarRepo = new repositories\calendar();

            $dateFrom = date("Y-m-d");
            $dateTo = date("Y-m-d");

            $tpl->assign('calendar', $calendarRepo->getCalendar($_SESSION['userdata']['id']));
            $tpl->assign('gCalLink', $calendarRepo->getMyGoogleCalendars());

            $_SESSION['lastPage'] = "/calendar/showMyCalendar/";

            //ToDO: This should come from the ticket repo...
            //$tpl->assign('ticketEditDates', $calendarRepo->getTicketEditDates());
            //$tpl->assign('ticketWishDates', $calendarRepo->getTicketWishDates());
            //$tpl->assign('dates', $calendarRepo->getAllDates($dateFrom, $dateTo));


            // 2022-09-28
            // 2022-09-30
            //Prepare ICS on load
            $cal_data = ($calendarRepo->getCalendar($_SESSION['userdata']['id']));
            $icsFile = fopen($config->icsFilePath, "wa+");

            $eol = '\r\n';
            $txt = "BEGIN:VCALENDAR\r
CALSCALE:GREGORIAN\r
METHOD:PUBLISH\r
PRODID:-//LeanTime Cal//EN\r
VERSION:2.0\r";

            //Template of Event
            // BEGIN:VEVENT
            // UID:ticket-16
            // DTSTART;VALUE=DATE:20220928
            // DTEND;VALUE=DATE:20220928
            // SUMMARY:235340
            // ESCRIPTION:''
            // LAST-MODIFIED:20220929T155340Z
            // END:VEVENT


            $event='';
            if (count($cal_data) > 0) {
                for ($i = 0; $i < count($cal_data); $i++) {
                    $dateFromy = $cal_data[$i]['dateFrom']['y'];
                    $dateFromm = $cal_data[$i]['dateFrom']['m'];
                    $dateFromd = $cal_data[$i]['dateFrom']['d'];
                    $dateFrom = $dateFromy . $dateFromm . $dateFromd;

                    $dateToy = $cal_data[$i]['dateTo']['y'];
                    $dateTom = $cal_data[$i]['dateTo']['m'];
                    $dateTod = $cal_data[$i]['dateTo']['d'];
                    $dateTo = $dateToy . $dateTom . $dateTod;

                   // if ($dateFromy > 0) { // if No Date Set -0001 may occur
                        $id = $cal_data[$i]['id'];
                        $title = $cal_data[$i]['title'];
                        $updatedAt =  isset($cal_data[$i]) && isset($cal_data[$i]['updatedAt']) ? $cal_data[$i]['updatedAt'] : '';
                        $lastMod = $updatedAt ? "LAST-MODIFIED:$updatedAt\r" : '';

                        $event =$event. "BEGIN:VEVENT\r
UID:ticket@$id\r
DTSTART;VALUE=DATE:$dateFrom\r
DTEND;VALUE=DATE:$dateTo\r
SUMMARY:$title\r
ESCRIPTION:''\r
$lastMod
END:VEVENT\r";
                   // }
                }
            }




            $result = $txt.$event."END:VCALENDAR";

            // echo ($result);
            fwrite($icsFile, $result) or die('fwrite failed');
            fclose($icsFile);
            $tpl->display('calendar.showMyCalendar');
        }
    }
}
