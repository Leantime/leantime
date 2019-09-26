<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class delTime
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $timesheetsRepo = new repositories\timesheets();

            $msgKey = '';

            if (isset($_GET['id']) === true) {

                $id = ($_GET['id']);

                //Delete User
                if (isset($_POST['del']) === true) {

                    $timesheetsRepo->deleteTime($id);

                    $msgKey = 'TIME_DELETED';

                }

                //Assign variables
                $tpl->assign('msg', $msgKey);


                $tpl->display('timesheets.delTime');

            } else {

                $tpl->display('general.error');

            }


        }

    }
}

