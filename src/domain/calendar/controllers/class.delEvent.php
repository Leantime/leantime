<?php

/**
 * delClient Class - Deleting clients
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class delEvent
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
