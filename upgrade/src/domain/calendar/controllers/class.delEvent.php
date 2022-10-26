<?php

/**
 * delClient Class - Deleting clients
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class delEvent
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

                if (isset($_POST['del']) === true) {

                    if($calendarRepo->delPersonalEvent($id) == true) {

                        $tpl->setNotification('notification.event_removed_successfully', 'success');

                    }else{

                        $tpl->setNotification('notification.could_not_delete_event', 'success');

                    }

                }

                $tpl->display('calendar.delEvent');

            } else {

                $tpl->display('general.error');

            }


        }

    }

}
