<?php

namespace leantime\domain\controllers {

    /**
     * delUser Class - Deleting users
     *
     */

    use leantime\core;
    use leantime\base\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class delGCal extends controller
    {

        private $calendarRepo;

        /**
         * init - initialize private variables
         */
        public function init()
        {

            $this->calendarRepo = new repositories\calendar();

        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {
            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);

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
                $this->tpl->display('calendar.delGCal');

            } else {

                $this->tpl->display('general.error');

            }

        }

    }
}
