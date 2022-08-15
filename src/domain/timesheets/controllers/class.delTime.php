<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class delTime
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor], true);

            $tpl = new core\template();
            $timesheetsRepo = new repositories\timesheets();

            $msgKey = '';

            if (isset($_GET['id']) === true) {

                $id = (int)($_GET['id']);

                //Delete User
                if (isset($_POST['del']) === true) {

                    $timesheetsRepo->deleteTime($id);

                   $tpl->setNotification("notifications.time_deleted_successfully", "success");

                    if(isset($_SESSION['lastPage'])) {
                        $tpl->redirect($_SESSION['lastPage']);
                    }else{
                        $tpl->redirect(BASE_URL."/timsheets/showMyList");
                    }

                }

                $tpl->assign("id", $id);
                $tpl->displayPartial('timesheets.delTime');

            } else {

                $tpl->displayPartial('general.error');

            }


        }

    }
}

