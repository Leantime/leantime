<?php

namespace Leantime\Domain\Calendar\Controllers {

    /**
     * delUser Class - Deleting users
     *
     */
    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
    use Leantime\Domain\Auth\Services\Auth;

    /**
     *
     */
    class DelGCal extends Controller
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

            if (isset($_GET['id']) === true) {
                $id = (int)($_GET['id']);

                $msgKey = '';

                //Delete User
                if (isset($_POST['del']) === true) {
                    $this->calendarRepo->deleteGCal($id);

                    $msgKey = 'Kalender gelöscht';
                }

                //Assign variables

                $this->tpl->assign('msg', $msgKey);
                return $this->tpl->display('calendar.delGCal');
            } else {
                return $this->tpl->display('errors.error403', responseCode: 403);
            }
        }
    }
}
