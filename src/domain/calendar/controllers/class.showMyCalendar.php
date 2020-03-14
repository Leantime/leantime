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

            $tpl = new core\template();
            $calendarRepo = new repositories\calendar();

            $tpl->assign('calendar', $calendarRepo->getCalendar($_SESSION['userdata']['id']));
            //$tpl->assign('gCalLink', $calendarRepo->getMyGoogleCalendars());

            $_SESSION['lastPage'] = BASE_URL."/calendar/showMyCalendar/";

            //ToDO: This should come from the ticket repo...
            //$tpl->assign('ticketEditDates', $calendarRepo->getTicketEditDates());
            //$tpl->assign('ticketWishDates', $calendarRepo->getTicketWishDates());
            //$tpl->assign('dates', $calendarRepo->getAllDates($dateFrom, $dateTo));

            $tpl->display('calendar.showMyCalendar');

        }

    }

}
