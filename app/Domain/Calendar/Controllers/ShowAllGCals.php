<?php

namespace Leantime\Domain\Calendar\Controllers {

    /**
     * newUser Class - show all User
     *
     */

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
    use Leantime\Domain\Auth\Services\Auth;

    /**
     *
     */
    class ShowAllGCals extends Controller
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

            //Assign vars
            $this->tpl->assign('allCalendars', $this->calendarRepo->getMyGoogleCalendars());

            return $this->tpl->display('calendar.showAllGCals');
        }
    }
}
