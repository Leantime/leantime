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

    class EditGCal extends Controller
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

            $msgKey = '';

            if (isset($_GET['id']) === true) {
                $id = ($_GET['id']);

                $row = $this->calendarRepo->getGCal($id);

                $values = array(
                    'url' => $row['url'],
                    'name' => $row['name'],
                    'colorClass' => $row['colorClass'],
                );

                if (isset($_POST['save']) === true) {
                    $values = array(
                        'url' => ($_POST['url']),
                        'name' => ($_POST['name']),
                        'colorClass' => ($_POST['color']),
                    );

                    $this->calendarRepo->editGUrl($values, $id);

                    $msgKey = 'Kalender bearbeitet';
                }

                $this->tpl->assign('values', $values);
                $this->tpl->assign('info', $msgKey);

                $this->tpl->display('calendar.editGCal');
            } else {
                $this->tpl->display('errors.error403');
            }
        }
    }
}
