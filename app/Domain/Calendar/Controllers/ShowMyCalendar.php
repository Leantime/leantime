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

    /**
     *
     */
    class ShowMyCalendar extends Controller
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
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            $this->tpl->assign('calendar', $this->calendarRepo->getCalendar($_SESSION['userdata']['id']));
            //$this->tpl->assign('gCalLink', $this->calendarRepo->getMyGoogleCalendars());

            $_SESSION['lastPage'] = BASE_URL . "/calendar/showMyCalendar/";

            $this->tpl->assign('externalCalendars', $this->calendarRepo->getMyExternalCalendars($_SESSION['userdata']['id']));

            //ToDO: This should come from the ticket repo...
            //$this->tpl->assign('ticketEditDates', $this->calendarRepo->getTicketEditDates());
            //$this->tpl->assign('ticketWishDates', $this->calendarRepo->getTicketWishDates());
            //$this->tpl->assign('dates', $this->calendarRepo->getAllDates($dateFrom, $dateTo));

            return $this->tpl->display('calendar.showMyCalendar');
        }
    }
}
