<?php

/**
 * showAll Class - show My Calender
 *
 */

namespace Leantime\Domain\Calendar\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Core\Frontcontroller;

    /**
     *
     */
    class Ical extends Controller
    {
        private CalendarRepository $calendarRepo;

        /**
         * init - initialize private variables
         */
        public function init(CalendarRepository $calendarRepo)
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
                return Frontcontroller::redirect(BASE_URL . "/errors/404");
            }

            $calendar = $this->calendarRepo->getCalendarBySecretHash($idParts[1], $idParts[0]);

            $this->tpl->assign("calendar", $calendar);

            header('Content-type: text/calendar; charset=utf-8');
            header('Content-disposition: attachment;filename="leantime.ics"');
            return $this->tpl->display("calendar.ical", "blank");
        }
    }

}
