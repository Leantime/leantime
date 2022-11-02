<?php

/**
 * showAll Class - show My Calender
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\events;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class ical
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $calId = $_GET['id'];

            $idParts = explode("_", $calId);
            $tpl = new core\template();

            if(count($idParts) != 2) {
                $tpl->redirect(BASE_URL."/errors/404");
            }

            $calendarRepo = new repositories\calendar();

            events::dispatch_event('begin', [
                'this' => $this,
                'tplInstance' => $tpl,
                'calendarRepo' => $calendarRepo,
            ]);

            $calendar = $calendarRepo->getCalendarBySecretHash($idParts[1], $idParts[0]);

            $tpl->assign("calendar", $calendar);

            header('Content-type: text/calendar; charset=utf-8');
            header('Content-disposition: attachment;filename="leantime.ics"');
            $tpl->display("calendar.ical", "blank");

            events::dispatch_event('end');

        }

    }

}
