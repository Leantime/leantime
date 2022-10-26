<?php

namespace leantime\domain\controllers {

    /**
     * delUser Class - Deleting users
     *
     */

    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class delGCal
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {
            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);

            $tpl = new core\template();
            $calendarRepo = new repositories\calendar();


            if (isset($_GET['id']) === true) {

                $id = (int)($_GET['id']);


                $msgKey = '';

                //Delete User
                if (isset($_POST['del']) === true) {

                    $calendarRepo->deleteGCal($id);

                    $msgKey = 'Kalender gelöscht';

                }

                //Assign variables

                $tpl->assign('msg', $msgKey);
                $tpl->display('calendar.delGCal');

            } else {

                $tpl->display('general.error');

            }


        }

    }
}
