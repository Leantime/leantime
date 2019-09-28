<?php

namespace leantime\domain\controllers {

    /**
     * newUser Class - show all User
     *
     */

    use leantime\core;
    use leantime\domain\repositories;

    class showAllGCals
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

            //Assign vars
            $tpl->assign('allCalendars', $calendarRepo->getMyGoogleCalendars());


            $tpl->display('calendar.showAllGCals');


        }

    }
}

