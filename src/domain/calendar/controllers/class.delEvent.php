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

                    $calendarRepo->delEvent($id);

                    $msgKey = 'Event was removed';

                }


                $tpl->assign('msg', $msgKey);

                $tpl->display('calendar.delEvent');

            } else {

                $tpl->display('general.error');

            }


        }

    }

}
