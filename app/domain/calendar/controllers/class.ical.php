<?php

/**
 * showAll Class - show My Calender
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class ical extends controller
    {
        private repositories\calendar $calendarRepo;

        /**
         * init - initialize private variables
         */
        public function init(repositories\calendar $calendarRepo)
        {
            $this->calendarRepo = $calendarRepo;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $calId = $_GET['id'];

            $idParts = explode("_", $calId);

            if (count($idParts) != 2) {
                $this->tpl->redirect(BASE_URL . "/errors/404");
            }

            $calendar = $this->calendarRepo->getCalendarBySecretHash($idParts[1], $idParts[0]);

            $this->tpl->assign("calendar", $calendar);

            header('Content-type: text/calendar; charset=utf-8');
            header('Content-disposition: attachment;filename="leantime.ics"');
            $this->tpl->display("calendar.ical", "blank");
        }
    }

}
