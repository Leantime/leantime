<?php

namespace Leantime\Domain\Calendar\Controllers {

    /**
     * importGCal Class - Add a new client
     *
     */

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
    use Leantime\Domain\Auth\Services\Auth;

    /**
     *
     */
    class ImportGCal extends Controller
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

            $values = array(
                'url' => '',
                'name' => '',
                'colorClass' => '',
            );

            if (isset($_POST['name']) === true) {
                $values = array(
                    'url' => ($_POST['url']),
                    'name' => ($_POST['name']),
                    'colorClass' => ($_POST['colorClass']),
                );

                $this->calendarRepo->addGUrl($values);
                $this->tpl->setNotification('notification.gcal_imported_successfully', 'success', 'externalcalendar_created');
            }

            $this->tpl->assign('values', $values);

            return $this->tpl->displayPartial('calendar.importGCal');
        }
    }
}
